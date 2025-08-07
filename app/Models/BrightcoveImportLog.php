<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrightcoveImportLog extends Model
{
    use HasFactory;

    protected $casts = [
        'data_log' => 'array',
    ];

    public function setDataLogAttribute($value) {
        $this->attributes['data_log'] = json_encode($value);
    }

    public static function startNewLog() {
        $log = new BrightcoveImportLog();
        $log->data_log = [];
        $log->save();
        return $log;
    }

    public function addVideoExists($video_meta, $existing_ccds_id) {
        $data_log = $this->data_log;
        $data_log['video_exists_skipping'][] = [
            'brightcove_id'=>$video_meta['id'],
            'title'=>$video_meta['name'],
            'ccds_id'=>$existing_ccds_id
        ];
        $this->data_log = $data_log;
        $this->save();
    }

    public function addVideoNotFound($video_meta) {
        $data_log = $this->data_log;
        $data_log['json_found_missing_video'][] = [
            'id'=>$video_meta['id'],
            'title'=>$video_meta['name']
        ];
        $this->data_log = $data_log;
        $this->save();
    }

    public function addVideoCreated($video_meta, $ccds_id) {

        $data_log = $this->data_log;
        $data_log['new'][] = [
            'id'=>$video_meta['id'],
            'title'=>$video_meta['name'],
            'ccds_id'=>$ccds_id
        ];
        $this->data_log = $data_log;
        $this->save();

    }

    public function addVideoError($video_meta) {
        $data_log = $this->data_log;
        $data_log['error'][] = [
            'id'=>$video_meta['id'],
            'title'=>$video_meta['name']
        ];
        $this->data_log = $data_log;
        $this->save();
    }

}
