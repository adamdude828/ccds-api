<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Grade;

class GradeTest extends TestCase
{
    /**
     * Test model has correct table name.
     */
    public function test_model_has_correct_table_name()
    {
        $grade = new Grade();
        
        $this->assertEquals('grades', $grade->getTable());
    }
    
    /**
     * Test model has fillable attributes.
     */
    public function test_model_has_fillable_attributes()
    {
        $grade = new Grade();
        $fillable = $grade->getFillable();
        
        $this->assertContains('name', $fillable);
    }
    
    /**
     * Test model connection name.
     */
    public function test_model_connection_name()
    {
        $grade = new Grade();
        
        // When no connection is specified, getConnectionName() returns null
        // which means it uses the default connection
        $this->assertNull($grade->getConnectionName());
    }
} 