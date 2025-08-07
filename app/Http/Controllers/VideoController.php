<?php

namespace App\Http\Controllers;

use App\Classes\Azure;
use App\Classes\AzureStorage;
use App\Classes\AzureStorageSAS;

use App\Http\Requests\Videos\MetaVideoRequest;
use App\Http\Requests\Videos\VideoEditRequest;
use App\Models\Subject;
use App\Models\Video;
use App\Models\VideoMode;
use App\Models\VideoStatus;
use App\Services\Mediakind\Mediakind;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class VideoController extends Controller
{

    public function __construct() {
        $this->middleware('auth:api', ['except'=>'view']);
    }

    public function store(Request $request) {



        if (!$request->has("videos")) {
            return $this->fail('Request does not have key videos set.');
        }

        $videos = $request->get("videos");
        if (!is_array($videos)) {
            return $this->fail("Videos key on request is not an array.");
        }

        $az_storage = new AzureStorage();
        $az_storage->setConnection(config("azure.storage.connection"));
        $azure = new Azure();
        $media_kind_client = new Mediakind();

        $saved_files = collect([]);
        $status = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['UPLOAD_IN_PROGRESS'])->first();
        foreach($videos as $video) {
            if (empty($video['file'])) {
                return $this->fail("Found video that does not have file key set.");
            }

            $file_info = pathinfo($video['file']);
            $video['title'] = str_replace(" ", "_", $file_info['filename']);
            /** @var Video  $video_in_progress */
            $video_in_progress = Video::create($video);
            $video_in_progress->initMeta();
            VideoMode::updateMode(VideoMode::READYMODE, $video_in_progress);
            $uid = Uuid::uuid4();
            $video_in_progress->uid = $uid->toString();
            $video_in_progress->uploader_user_id = auth()->user()->id;
            $meta = $video_in_progress->meta;

            $guid = $video_in_progress->getInputGuid();
            Log::error("create asset", [
                'input_asset' => $video_in_progress->getInputAssetName(),
                'input_container'=>$video_in_progress->getInputContainer(),
            ]);
            $media_kind_client->createAsset(
                $video_in_progress->getInputAssetName(),
                $video_in_progress->getInputContainer()
            );
//            $azure->createAsset(
//                $video_in_progress->getInputAssetName(),
//                $video_in_progress->getInputContainer()
//            );
            Log::error("create asset done", [
                'input_asset' => $video_in_progress->getInputAssetName(),
                'input_container'=>$video_in_progress->getInputContainer(),
            ]);

            $az_storage->createContainer(
                $video_in_progress->getInputContainer(),
            );

            $az_storage->createContainer(
                $video_in_progress->getOutputPosterContainer()
            );

            $sas_generator = new AzureStorageSAS();
            $meta['input']['sas'] = $sas_generator->getSAS(
                $video_in_progress->getInputContainer(),
                $video_in_progress->getInputFile()
            );
            $meta['input']['poster-sas'] = $sas_generator->getSAS(
                $video_in_progress->getOutputPosterContainer(),
                $video_in_progress->getOutputPosterImage()
            );
            $video_in_progress->poster = $video_in_progress->getOutputPosterImage();
            //$meta['input']['container'] = $guid;
            $meta['input']['account'] = $az_storage->getAccountName();
            $meta['input']['original_file'] = $video['file'];
            $video_in_progress->meta = $meta;
            $video_in_progress->video_status_id = $status->id;
            $video_in_progress->save();

            $saved_files->push($video_in_progress);
        }

        return $this->successWithModels(
            $saved_files
        );

    }

    public function view(Request $request, Video $video) {
        return $this->model($video);
    }

    public function edit( VideoEditRequest $request, Video $video) {
        $validated = $request->validated();
        $video->title = $validated['title'];
        $video->day = $validated['day'];
        $video->subject_id = $validated['subject_id'];
        $video->grade_id = $validated['grade_id'];
        $video->description = $validated['description'];
        $video->video_mode_id = $validated['video_mode_id'];
        $video->save();

        return $this->success([]);
    }

    public function UploadComplete(Request $request) {
        $videos = $request->get("videos");
        if (!is_array($videos)) {
            $this->fail("Videos was not specifid or not array.");
        }

        try {
            DB::beginTransaction();

            $complete_status = VideoStatus::where("name", VideoStatus::VIDEO_STATUS['UPLOAD_COMPLETE'])->first();
            foreach($videos as $video) {
                $video_found = Video::find($video);
                if ($video_found == null) {
                    DB::rollBack();
                    $this->fail("Video with id $video was not found in the db.");
                    return;
                }

                $video_found->video_status_id = $complete_status->id;
                $video_found->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->fail($e->getMessage());
        }
    }

    public function index(Request $request) {

        $query = Video::query()
            ->with('grade')
            ->with('subject')
            ->with("video_status")
            ->with('uploader');

        if ($request->has('q')) {
            $query->where("title", "like", "%" . $request->get("q") . "%");
        }

        if ($request->has("sort")) {
            if (!$request->has("direction")) {
                $direction = 'desc';
            } else {
                $direction = $request->get("direction");
            }
            $query->orderBy($request->get("sort"), $direction);
        }

        $result = $query->paginate(50);
        $result->withPath("/pages/videos");


        return $this->successWithModels(
            $result
        );
    }

    public function delete(Request $request, Video $video) {
        $video->delete();
        return $this->success([]);
    }

    public function updateMeta(MetaVideoRequest $request) {
        //make sure all ids exist in the db
        $ids = $request->get("ids_to_update");
        $video_exists_count = Video::whereIn("id", $ids)->count();

        if ($video_exists_count !== count($ids)){
            return $this->fail('One or more video ids do not exist.');
        }

        //right now we only exist three update options, verify
        $update_columns = array_keys($request->get('data'));
        foreach($update_columns as $col) {
            if (!in_array($col, ['subject_id', 'video_mode_id', 'grade_id', 'day'])) {
                return $this->fail('One or more columns to update are not supported.');
            }
        }

        Video::whereIn("id", $ids)
            ->update($request->get('data'));
        return $this->success([]);
    }
    //
    //
}
