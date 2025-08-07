<?php

namespace App\Jobs\TranscodePoster;

use App\Classes\Azure;
use App\Classes\AzureStorage;
use App\Jobs\PosterJob;
use App\Models\Video;
use App\Models\VideoStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MicrosoftAzure\Storage\Blob\Models\Blob;

class TranscodePosterVideoFinish implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Video
     */
    protected $video;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($video)
    {
        $this->video = $video;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $meta = $this->video->meta;
            $azure = new Azure();
            $job_status = $azure->checkJob($this->video->meta['output']['poster-job-name']);

            if ($job_status['properties']['state'] == 'Error') {
                $error_status = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['VIDEO_ERROR'])->first();
                $this->video->video_status_id = $error_status->id;
                $this->video->save();
                return;
            }

            if ($job_status['properties']['state'] !== 'Finished') {
                dispatch(new TranscodePosterVideoFinish($this->video))->delay(now()->addMinutes(1));
                return;
            }

            $az_storage = new AzureStorage();
            $az_storage->setConnection(config("azure.storage.connection"));

            if ($job_status['properties']['state'] === 'Finished') {
                $files = $az_storage->listFiles($this->video->getOutputPosterContainer());
                $waiting_poster = VideoStatus::where('name', VideoStatus::VIDEO_STATUS['VIDEO_READY'])->first();
                $this->video->video_status_id = $waiting_poster->id;
                foreach($files->getBlobs() as $file) {
                    /** @var Blob $file */
                    $name = pathinfo($file->getName());
                    if ($name['extension'] == 'png') {
                        $this->video->poster = $file->getName();
                        $this->video->save();
                        break;
                    }
                }
                $this->video->save();

            }
        } catch(\Exception $e) {
            $error_params = [
                'video'=>$this->video,
                'error_message'=>$e->getMessage()
            ];
            if (method_exists($e, 'getResponse')) {
                $error_params['response'] = $e->getResponse()->getBody()->getContents();
            }

            \Log::emergency("Error during job creation process", $error_params);
            $error_status = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['VIDEO_ERROR'])->first();
            $this->video->video_status_id = $error_status->id;
            $this->video->save();

            throw $e;
        }





        //
    }
}
