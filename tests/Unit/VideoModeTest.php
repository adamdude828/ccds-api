<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Video;
use App\Models\VideoMode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoModeTest extends TestCase
{
    use RefreshDatabase;

    protected $readyMode;
    protected $draftMode;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Get existing video modes created by migration
        $this->readyMode = VideoMode::where('name', VideoMode::READYMODE)->first();
        $this->draftMode = VideoMode::where('name', VideoMode::DRAFTMODE)->first();
    }

    /**
     * Test update mode with valid mode name.
     */
    public function test_update_mode_with_valid_mode_name()
    {
        $video = Video::factory()->create();
        
        VideoMode::updateMode(VideoMode::DRAFTMODE, $video);
        
        $this->assertEquals($this->draftMode->id, $video->fresh()->video_mode_id);
    }

    /**
     * Test update mode throws exception with invalid mode.
     */
    public function test_update_mode_throws_exception_with_invalid_mode()
    {
        $video = Video::factory()->create();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Passed video mode that is not valid.');
        
        VideoMode::updateMode('invalid_mode', $video);
    }

    /**
     * Test is mode with mode name.
     */
    public function test_is_mode_with_mode_name()
    {
        $video = Video::factory()->create(['video_mode_id' => $this->readyMode->id]);
        
        $this->assertTrue(VideoMode::isMode(VideoMode::READYMODE, $video));
        $this->assertFalse(VideoMode::isMode(VideoMode::DRAFTMODE, $video));
    }

    /**
     * Test is mode with mode id.
     */
    public function test_is_mode_with_mode_id()
    {
        $video = Video::factory()->create(['video_mode_id' => $this->draftMode->id]);
        
        $this->assertTrue(VideoMode::isMode($this->draftMode->id, $video));
        $this->assertFalse(VideoMode::isMode($this->readyMode->id, $video));
    }

    /**
     * Test is mode throws exception with invalid mode name.
     */
    public function test_is_mode_throws_exception_with_invalid_mode_name()
    {
        $video = Video::factory()->create();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Passed video mode that is not valid.');
        
        VideoMode::isMode('invalid_mode', $video);
    }

    /**
     * Test is mode throws exception with invalid mode id.
     */
    public function test_is_mode_throws_exception_with_invalid_mode_id()
    {
        $video = Video::factory()->create();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Passed video mode that is not valid.');
        
        VideoMode::isMode(999, $video);
    }

    /**
     * Test constants are defined.
     */
    public function test_constants_are_defined()
    {
        $this->assertEquals('ready', VideoMode::READYMODE);
        $this->assertEquals('draft', VideoMode::DRAFTMODE);
    }
} 