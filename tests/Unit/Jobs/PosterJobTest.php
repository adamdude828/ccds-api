<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\PosterJob;
use App\Models\Video;
use Illuminate\Support\Facades\Queue;

class PosterJobTest extends TestCase
{
    /**
     * Test job can be instantiated with video.
     */
    public function test_job_can_be_instantiated_with_video()
    {
        // Create a proper Video model instance
        $video = Video::factory()->create();
        
        $job = new PosterJob($video);
        
        $this->assertInstanceOf(PosterJob::class, $job);
    }

    /**
     * Test job can be dispatched.
     */
    public function test_job_can_be_dispatched()
    {
        Queue::fake();
        
        $video = Video::factory()->create();
        
        PosterJob::dispatch($video);
        
        Queue::assertPushed(PosterJob::class, function ($job) {
            return true;
        });
    }
} 