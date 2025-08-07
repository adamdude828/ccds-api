<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\TestJob;
use Illuminate\Support\Facades\Queue;

class TestJobTest extends TestCase
{
    /**
     * Test job can be instantiated.
     */
    public function test_job_can_be_instantiated()
    {
        $job = new TestJob();
        
        $this->assertInstanceOf(TestJob::class, $job);
    }

    /**
     * Test job can be dispatched.
     */
    public function test_job_can_be_dispatched()
    {
        Queue::fake();
        
        TestJob::dispatch();
        
        Queue::assertPushed(TestJob::class, 1);
    }
} 