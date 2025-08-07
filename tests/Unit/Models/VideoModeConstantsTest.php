<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\VideoMode;

class VideoModeConstantsTest extends TestCase
{
    /**
     * Test VideoMode constants are defined correctly.
     */
    public function test_video_mode_constants_are_defined()
    {
        $this->assertEquals('ready', VideoMode::READYMODE);
        $this->assertEquals('draft', VideoMode::DRAFTMODE);
    }

    /**
     * Test VideoMode has correct fillable fields.
     */
    public function test_video_mode_has_correct_fillable_fields()
    {
        $model = new VideoMode();
        
        $this->assertContains('name', $model->getFillable());
        $this->assertContains('id', $model->getFillable());
    }

    /**
     * Test VideoMode table name.
     */
    public function test_video_mode_table_name()
    {
        $model = new VideoMode();
        
        $this->assertEquals('video_modes', $model->getTable());
    }
} 