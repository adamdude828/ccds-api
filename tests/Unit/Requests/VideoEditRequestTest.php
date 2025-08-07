<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\Videos\VideoEditRequest;

class VideoEditRequestTest extends TestCase
{
    /**
     * Test validation rules are defined.
     */
    public function test_validation_rules_are_defined()
    {
        $request = new VideoEditRequest();
        $rules = $request->rules();
        
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('grade_id', $rules);
        $this->assertArrayHasKey('subject_id', $rules);
        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('video_mode_id', $rules);
        $this->assertArrayHasKey('day', $rules);
        
        // Test specific rules
        $this->assertEquals('nullable|exists:subjects,id', $rules['subject_id']);
        $this->assertEquals('nullable|exists:grades,id', $rules['grade_id']);
        $this->assertEquals('nullable|numeric', $rules['day']);
        $this->assertEquals('nullable', $rules['description']);
        $this->assertEquals('required', $rules['title']);
        $this->assertEquals('required', $rules['video_mode_id']);
    }

    /**
     * Test authorize method returns true.
     */
    public function test_authorize_returns_true()
    {
        $request = new VideoEditRequest();
        
        $this->assertTrue($request->authorize());
    }
} 