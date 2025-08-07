<?php

namespace Tests\Unit\Classes;

use Tests\TestCase;
use App\Classes\Azure;
use Illuminate\Support\Facades\Config;

class AzureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set required config
        Config::set('azure.storage.connection', 'DefaultEndpointsProtocol=https;AccountName=test;AccountKey=test==');
        Config::set('azure.storage.name', 'teststorage');
        Config::set('azure.storage.key', 'testkey==');
        Config::set('azure.storage.url', 'https://test.blob.core.windows.net/');
    }
    
    /**
     * Test Azure class can be instantiated.
     */
    public function test_azure_class_can_be_instantiated()
    {
        $azure = new Azure();
        
        $this->assertInstanceOf(Azure::class, $azure);
    }
    
    /**
     * Test get storage name method.
     */
    public function test_get_storage_name_returns_config_value()
    {
        $azure = new Azure();
        
        $this->assertEquals('teststorage', $azure->getStorageName());
    }
    
    /**
     * Test get storage key method.
     */
    public function test_get_storage_key_returns_config_value()
    {
        $azure = new Azure();
        
        $this->assertEquals('testkey==', $azure->getStorageKey());
    }
    
    /**
     * Test get storage URL method.
     */
    public function test_get_storage_url_returns_config_value()
    {
        $azure = new Azure();
        
        $this->assertEquals('https://test.blob.core.windows.net/', $azure->getStorageUrl());
    }
} 