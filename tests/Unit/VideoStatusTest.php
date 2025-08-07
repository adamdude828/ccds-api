<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Video;
use App\Models\VideoStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed video statuses from the constants
        $id = 1;
        foreach (VideoStatus::VIDEO_STATUS as $displayName => $name) {
            VideoStatus::create([
                'id' => $id++,
                'name' => $name,
                'display_name' => $displayName
            ]);
        }
    }

    /**
     * Test update status with valid status name.
     */
    public function test_update_status_with_valid_status_name()
    {
        $video = Video::factory()->create();
        $initialStatusId = $video->video_status_id;
        
        VideoStatus::updateStatus(VideoStatus::VIDEO_STATUS['VIDEO_READY'], $video);
        
        $this->assertNotEquals($initialStatusId, $video->fresh()->video_status_id);
        $this->assertEquals(
            VideoStatus::where('name', VideoStatus::VIDEO_STATUS['VIDEO_READY'])->first()->id,
            $video->fresh()->video_status_id
        );
    }

    /**
     * Test update status throws exception with invalid status.
     */
    public function test_update_status_throws_exception_with_invalid_status()
    {
        $video = Video::factory()->create();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Passed video mode that is not valid.');
        
        VideoStatus::updateStatus('invalid_status', $video);
    }

    /**
     * Test all status constants are defined correctly.
     */
    public function test_all_status_constants_are_defined()
    {
        $expectedStatuses = [
            'UPLOAD_IN_PROGRESS' => 'upload_in_progress',
            'UPLOAD_COMPLETE' => 'upload_complete',
            'QUEUED_TRANSCODE' => 'queued_transcode',
            'TRANSCODE_IN_PROGRESS' => 'transcode_in_progress',
            'TRANSCODE_COMPLETE' => 'transcode_complete',
            'QUEUED_FOR_POSTER' => 'waiting_for_poster',
            'POSTER_IN_PROGRESS' => 'poster_in_progress',
            'VIDEO_READY' => 'video_ready',
            'VIDEO_ERROR' => 'error',
            'VIDEO_DRAFT' => 'draft'
        ];
        
        $this->assertEquals($expectedStatuses, VideoStatus::VIDEO_STATUS);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $status = VideoStatus::create([
            'name' => 'test_status',
            'display_name' => 'Test Status'
        ]);
        
        $this->assertEquals('test_status', $status->name);
        $this->assertEquals('Test Status', $status->display_name);
    }
} 