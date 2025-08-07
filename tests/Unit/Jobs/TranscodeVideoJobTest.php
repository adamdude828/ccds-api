<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\Transcode\TranscodeVideoJob;
use Illuminate\Support\Facades\Queue;

class TranscodeVideoJobTest extends TestCase
{
    /**
     * Test job can be instantiated with video.
     */
    public function test_job_can_be_instantiated_with_video()
    {
        // Create a mock video object
        $video = new \stdClass();
        $video->id = 1;
        $video->title = 'Test Video';
        $video->guid = 'test-guid';
        
        $job = new TranscodeVideoJob($video);
        
        $this->assertInstanceOf(TranscodeVideoJob::class, $job);
    }

    /**
     * Test job can be dispatched.
     */
    public function test_job_can_be_dispatched()
    {
        Queue::fake();
        
        $video = new \stdClass();
        $video->id = 1;
        $video->guid = 'test-guid';
        
        TranscodeVideoJob::dispatch($video);
        
        Queue::assertPushed(TranscodeVideoJob::class, function ($job) {
            return true;
        });
    }

    /**
     * Test job has correct tags.
     */
    public function test_job_has_correct_tags()
    {
        $video = new \stdClass();
        $video->id = 123;
        $video->guid = 'test-guid-123';
        
        $job = new TranscodeVideoJob($video);
        
        $tags = $job->tags();
        
        $this->assertContains('Video:123', $tags);
    }
} 