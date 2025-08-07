<?php

namespace App\Models;

use App\Classes\AzureStorageSAS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Sujip\Guid\Guid;


class Video extends Model {

    use SoftDeletes, HasFactory;

    protected $casts = [
        'meta'=>'array'
    ];

    protected $fillable = [
        'video_id',
        'subject_id',
        'title',
        'day',
        'description',
        'guid',
        'streaming_url',
        'poster',
        'status_id',
        'mode_id'
    ];

    protected $appends = [
        'public_url',
        'private_url',
        'authenticated_poster',
        'source_url'
    ];

    public function getSourceUrlAttribute() {
        $storage = new AzureStorageSAS();
        $sas = $storage->generateBlobSAS(
            $this->getInputContainer(),
            $this->getInputFile(),
            'r'
        );
        return config("azure.storage.url") . $this->getInputContainer() . "/".  $this->getInputFile() .
            "?" . $sas;
    }

    public function getPrivateUrlAttribute() {
        if ($this->video_status->name == VideoStatus::VIDEO_STATUS['VIDEO_READY']) {
            return $this->getPublicUrlAttribute();
        }

        return $this->getSourceUrlAttribute();
    }

    public function getPublicUrlAttribute() {

        if (empty($this->streaming_url)) {
            return '';
        }

        return rtrim(config("azure.media.url"), "/")
            .  "/" . ltrim($this->streaming_url, '/');
    }

    public function getAuthenticatedPosterAttribute() {
        if (empty($this->poster)) {
            return '';
        }

        $poster_container = $this->getOutputPosterContainer();
        if (empty($poster_container)) {
            $poster_container = config("azure.storage.poster");
        }

        $storage = new AzureStorageSAS();
        $sas = $storage->generateBlobSAS(
            $poster_container,
            $this->poster,
            'r'
        );
        return config("azure.storage.url") . $poster_container . "/".  $this->poster .
                    "?" . $sas;
    }

    public function uploader() {
        return $this->belongsTo(User::class, 'uploader_user_id');
    }

    public function initMeta() {
        $meta = [];
        $uid = Uuid::uuid4();
        $meta['input']['guid'] = str_replace(".", "", $uid->toString());
        $meta['input']['container'] = "inc" . $meta['input']['guid'];
        $meta['input']['asset'] = $meta['input']['guid'] . "-IN";
        $meta['input']['job'] = $meta['input']['guid'] . "-JOB";
        $meta['input']['file'] = $meta['input']['guid'] . ".mp4";

        $meta['streaming-locator'] = $meta['input']['guid'] . "-STREAMING";

        $meta['output']['container'] = "outc" . $meta['input']['guid'];
        $meta['output']['asset'] = $meta['input']['guid'] . "-OUT";
        $meta['output']['poster-container'] = "outcp" . $meta['input']['guid'];
        $meta['output']['poster-asset'] = $meta['input']['guid'] . "-pout";
        $meta['output']['poster-image'] = 'POSTER-' . $meta['input']['guid'] . ".png";

        $this->meta = $meta;
        $this->save();
    }

    public function getInputFile() {
        return $this->meta['input']['file'];
    }

    public function getInputGuid() {
        return $this->meta['input']['guid'];
    }

    public function getInputContainer() {
        return $this->meta['input']['container'];
    }

    public function getOutputContainerName() {
        return $this->meta['output']['container'];
    }

    public function getInputAssetName() {
        return $this->meta['input']['asset'];
    }

    public function getOutputAssetName() {
        return $this->meta['output']['asset'];
    }

    public function getOutputPosterContainer() {
        return empty($this->meta['output']['poster-container']) ? null :
                        $this->meta['output']['poster-container'];
    }

    public function getOutputPosterAsset() {
        return $this->meta['output']['poster-asset'];
    }

    public function getOutputPosterImage() {
        return $this->meta['output']['poster-image'];
    }

    public function getJobName() {
        return $this->meta['input']['job'];
    }

    public function getStreamingLocatorName() {
        return $this->meta['streaming-locator'];
    }

    public function grade() {
        return $this->belongsTo(Grade::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    public function video_status() {
        return $this->belongsTo(VideoStatus::class);
    }

    public function scopeOfType($query, $type) {
        $status = VideoStatus::where("name", $type)->first();
        return $query->where("video_status_id", $status->id);
    }
}
