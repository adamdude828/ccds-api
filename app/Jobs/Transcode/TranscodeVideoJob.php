<?php

namespace App\Jobs\Transcode;

use App\Classes\Azure;
use App\Classes\AzureStorage;
use App\Models\Video;
use App\Models\VideoStatus;
use App\Services\Mediakind\Mediakind;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranscodeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Video
     */
    protected $video = null;

    public $tries = 1;

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
            $in_progress = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['TRANSCODE_IN_PROGRESS'])->first();
            $this->video->video_status_id = $in_progress->id;
            $this->video->save();

            $az_storage = new AzureStorage();
            $az_storage->setConnection(config("azure.storage.connection"));

            $mediaKind = new Mediakind();
            $mediaKind->createAsset($this->video->getOutputAssetName(), $this->video->getOutputContainerName());
            $mediaKind->createJob(
                $this->video->getInputAssetName(),
                $this->video->getOutputAssetName(),
                $this->video->getJobName(),
                $this->video->getInputFile()
            );

//            $az = new Azure();
//            $az->createAsset($this->video->getOutputAssetName(), $this->video->getOutputContainerName());
//            $az->createAsset($this->video->getOutputPosterAsset(), $this->video->getOutputPosterContainer());
//            $az->createJob(
//                $this->video->getInputAssetName(),
//                $this->video->getOutputAssetName(),
//                $this->video->getOutputPosterAsset(),
//                $this->video->getJobName()
//            );
            dispatch(new TranscodeVideoFinish($this->video))->delay(now()->addMinutes(1));
        } catch(\Exception $e) {

            $error_params = [
                'video'=>$this->video,
                'error_message'=>$e->getMessage()
            ];
            if ($e->getResponse()) {
                $error_params['response'] = $e->getResponse()->getBody()->getContents();
            }

            \Log::emergency("Error during job creation process", $error_params);
            $error_status = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['VIDEO_ERROR'])->first();
            $this->video->video_status_id = $error_status->id;
            $this->video->save();

            throw $e;
        }

    }
    
    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['Video:' . $this->video->id];
    }
}
