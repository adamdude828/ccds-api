<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\TranscodePoster\TranscodePosterVideoJob;
use Illuminate\Support\Facades\Queue;

class TranscodePosterVideoJobTest extends TestCase
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
        
        $job = new TranscodePosterVideoJob($video);
        
        $this->assertInstanceOf(TranscodePosterVideoJob::class, $job);
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
        
        TranscodePosterVideoJob::dispatch($video);
        
        Queue::assertPushed(TranscodePosterVideoJob::class, function ($job) {
            return true;
        });
    }

    /**
     * Test job has correct tags.
     */
    public function test_job_has_correct_tags()
    {
        $video = new \stdClass();
        $video->id = 456;
        $video->guid = 'test-guid-456';
        
        $job = new TranscodePosterVideoJob($video);
        
        $tags = $job->tags();
        
        $this->assertContains('Video:456', $tags);
    }
} 