<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\Videos\MetaVideoRequest;

class MetaVideoRequestTest extends TestCase
{
    /**
     * Test validation rules are defined.
     */
    public function test_validation_rules_are_defined()
    {
        $request = new MetaVideoRequest();
        $rules = $request->rules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
    }

    /**
     * Test authorize method returns true.
     */
    public function test_authorize_returns_true()
    {
        $request = new MetaVideoRequest();
        
        $this->assertTrue($request->authorize());
    }
} 