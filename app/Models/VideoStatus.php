<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name'
    ];

    const VIDEO_STATUS = [
        'UPLOAD_IN_PROGRESS' => 'upload_in_progress',
        "UPLOAD_COMPLETE" => 'upload_complete',
        "QUEUED_TRANSCODE" => "queued_transcode",
        'TRANSCODE_IN_PROGRESS' => 'transcode_in_progress',
        "TRANSCODE_COMPLETE" => "transcode_complete",
        "QUEUED_FOR_POSTER" => "waiting_for_poster",
        "POSTER_IN_PROGRESS" => "poster_in_progress",
        'VIDEO_READY' => 'video_ready',
        'VIDEO_ERROR' => 'error',
        'VIDEO_DRAFT' => 'draft'
    ];

    public static function updateStatus($status, $video) {
        $statusObj = self::where("name", $status)->first();
        if (empty($statusObj)) {
            throw new \Exception("Passed video mode that is not valid.");
        }

        $video->video_status_id = $statusObj->id;
        $video->save();
    }
}
