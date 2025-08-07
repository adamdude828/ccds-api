<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BrightcoveImportLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrightcoveImportLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test start new log creates an instance.
     */
    public function test_start_new_log_creates_instance()
    {
        $log = BrightcoveImportLog::startNewLog();
        
        $this->assertInstanceOf(BrightcoveImportLog::class, $log);
        $this->assertIsArray($log->data_log);
        $this->assertEmpty($log->data_log);
    }

    /**
     * Test add video exists method.
     */
    public function test_add_video_exists()
    {
        $log = BrightcoveImportLog::startNewLog();
        
        $videoMeta = [
            'id' => 'BC123',
            'name' => 'Test Video'
        ];
        $existingCcdsId = 'CCDS456';
        
        $log->addVideoExists($videoMeta, $existingCcdsId);
        
        $this->assertArrayHasKey('video_exists_skipping', $log->data_log);
        $this->assertCount(1, $log->data_log['video_exists_skipping']);
        $this->assertEquals('BC123', $log->data_log['video_exists_skipping'][0]['brightcove_id']);
        $this->assertEquals('Test Video', $log->data_log['video_exists_skipping'][0]['title']);
        $this->assertEquals('CCDS456', $log->data_log['video_exists_skipping'][0]['ccds_id']);
    }

    /**
     * Test add video not found method.
     */
    public function test_add_video_not_found()
    {
        $log = BrightcoveImportLog::startNewLog();
        
        $videoMeta = [
            'id' => 'BC789',
            'name' => 'Missing Video'
        ];
        
        $log->addVideoNotFound($videoMeta);
        
        $this->assertArrayHasKey('json_found_missing_video', $log->data_log);
        $this->assertCount(1, $log->data_log['json_found_missing_video']);
        $this->assertEquals('BC789', $log->data_log['json_found_missing_video'][0]['id']);
        $this->assertEquals('Missing Video', $log->data_log['json_found_missing_video'][0]['title']);
    }

    /**
     * Test add video created method.
     */
    public function test_add_video_created()
    {
        $log = BrightcoveImportLog::startNewLog();
        
        $videoMeta = [
            'id' => 'BC999',
            'name' => 'New Video'
        ];
        $ccdsId = 'CCDS111';
        
        $log->addVideoCreated($videoMeta, $ccdsId);
        
        $this->assertArrayHasKey('new', $log->data_log);
        $this->assertCount(1, $log->data_log['new']);
        $this->assertEquals('BC999', $log->data_log['new'][0]['id']);
        $this->assertEquals('New Video', $log->data_log['new'][0]['title']);
        $this->assertEquals('CCDS111', $log->data_log['new'][0]['ccds_id']);
    }

    /**
     * Test add video error method.
     */
    public function test_add_video_error()
    {
        $log = BrightcoveImportLog::startNewLog();
        
        $videoMeta = [
            'id' => 'BC_ERROR',
            'name' => 'Error Video'
        ];
        
        $log->addVideoError($videoMeta);
        
        $this->assertArrayHasKey('error', $log->data_log);
        $this->assertCount(1, $log->data_log['error']);
        $this->assertEquals('BC_ERROR', $log->data_log['error'][0]['id']);
        $this->assertEquals('Error Video', $log->data_log['error'][0]['title']);
    }

    /**
     * Test data log is cast to array.
     */
    public function test_data_log_is_cast_to_array()
    {
        $log = new BrightcoveImportLog();
        $log->data_log = ['test' => 'data'];
        $log->save();
        
        $freshLog = BrightcoveImportLog::find($log->id);
        $this->assertIsArray($freshLog->data_log);
        $this->assertEquals(['test' => 'data'], $freshLog->data_log);
    }
} 