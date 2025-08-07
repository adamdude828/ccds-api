<?php

namespace App\Jobs;

use App\Classes\AzureStorage;
use App\Models\Video;
use App\Models\VideoStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;

class PosterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * @var Video
     */
    protected $video;

    public $tries = 10000;

    public $timeout = 3600;

    public function retryUntil() {
        return now()->addHours(12);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }


    public function middleware()
    {
        return [(new WithoutOverlapping(getenv("NODE_ID")))->releaseAfter(120)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            \Log::emergency("Poster start", [
                'id'=>$this->video->id,
                'title'=>$this->video->title
            ]);
            if ($this->video->poster !== null) {
                return;
            }

            $poster_progress = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['POSTER_IN_PROGRESS'])->first();
            $this->video->video_status_id = $poster_progress->id;
            $this->video->save();

            $az_storage = new AzureStorage();
            $az_storage->setConnection(config("azure.storage.connection"));

            $tmp_location_video = "/tmp/".uniqid() . ".mp4";
            $tmp_location_image = "/tmp/".uniqid() . '.png';
            $az_storage->downloadFile(
                $this->video->getInputContainer(),
                $this->video->getInputFile(),
                $tmp_location_video
            );

            exec(
                "ffmpeg -i \"$tmp_location_video\" -vf \"select=eq(n\\,55)\" -vframes 1 \"$tmp_location_image\" ",
                $output,
                $result
            );

            if ($result !== 0) {
                \Log::emergency("ffmpeg command to extract image failed", [
                    'video'=>$this->video->id,
                    'std_error'=>implode("\n", $output)
                ]);
                if (file_exists($tmp_location_video)) {
                    unlink($tmp_location_video);
                }

                if (file_exists($tmp_location_image)) {
                    unlink($tmp_location_image);
                }
                return;
            }

            $video_status = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['VIDEO_READY'])->first();
            $this->video->video_status_id = $video_status->id;
            $this->video->poster = $az_storage->uploadPoster($tmp_location_image);
            $this->video->save();

            if (file_exists($tmp_location_video)) {
                unlink($tmp_location_video);
            }

            if (file_exists($tmp_location_image)) {
                unlink($tmp_location_image);
            }
            \Log::emergency("Poster end", [
                'id'=>$this->video->id,
                'title'=>$this->video->title
            ]);
    }

}
