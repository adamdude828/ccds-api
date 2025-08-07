<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Mediakind\Mediakind;
use Illuminate\Support\Facades\Config;

class MediakindTest extends TestCase
{
    protected $mediakind;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock config values
        Config::set('mediakind.base_url', 'https://api.mediakind.test');
        Config::set('mediakind.project_name', 'test-project');
        Config::set('mediakind.key', 'test-api-key');
        Config::set('mediakind.transform', 'test-transform');
        
        $this->mediakind = new Mediakind();
    }
    
    /**
     * Test Mediakind can be instantiated.
     */
    public function test_mediakind_can_be_instantiated()
    {
        $this->assertInstanceOf(Mediakind::class, $this->mediakind);
    }
    
    /**
     * Test constructor sets base URL from config.
     */
    public function test_constructor_sets_base_url_from_config()
    {
        // Use reflection to access private property
        $reflection = new \ReflectionClass($this->mediakind);
        $property = $reflection->getProperty('baseUrl');
        $property->setAccessible(true);
        
        $this->assertEquals('https://api.mediakind.test', $property->getValue($this->mediakind));
    }
} 