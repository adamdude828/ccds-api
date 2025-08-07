<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoMode extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'id'
    ];

    const READYMODE='ready';
    const DRAFTMODE='draft';

    public static function updateMode($mode, $video) {
        $modeObj = self::where("name", $mode)->first();
        if (empty($modeObj)) {
            throw new \Exception("Passed video mode that is not valid.");
        }

        $video->video_mode_id = $modeObj->id;
        $video->save();
    }

    public static function isMode($mode, $video) {
        if (is_integer($mode) === false) {
            $modeObj = self::where("name", $mode)->first();
        } else {
            $modeObj = self::find($mode);
        }

        if (empty($modeObj)) {
            throw new \Exception("Passed video mode that is not valid.");
        }

        return $modeObj->id == $video->video_mode_id;
    }
}
