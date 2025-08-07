<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Subject;

class SubjectTest extends TestCase
{
    /**
     * Test model has correct table name.
     */
    public function test_model_has_correct_table_name()
    {
        $subject = new Subject();
        
        $this->assertEquals('subjects', $subject->getTable());
    }
    
    /**
     * Test model has fillable attributes.
     */
    public function test_model_has_fillable_attributes()
    {
        $subject = new Subject();
        $fillable = $subject->getFillable();
        
        $this->assertContains('name', $fillable);
    }
    
    /**
     * Test model key name.
     */
    public function test_model_key_name()
    {
        $subject = new Subject();
        
        $this->assertEquals('id', $subject->getKeyName());
    }
    
    /**
     * Test model incrementing.
     */
    public function test_model_incrementing()
    {
        $subject = new Subject();
        
        $this->assertTrue($subject->getIncrementing());
    }
} 