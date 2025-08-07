<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\AzureStorageServiceProvider;
use Illuminate\Support\Facades\Config;

class AzureStorageServiceProviderTest extends TestCase
{
    /**
     * Test service provider registers services.
     */
    public function test_service_provider_registers_services()
    {
        // Set required config
        Config::set('azure.storage.connection', 'DefaultEndpointsProtocol=https;AccountName=test;AccountKey=test');
        
        $provider = new AzureStorageServiceProvider($this->app);
        
        // Call register method
        $provider->register();
        
        // Check that services are registered
        $this->assertTrue($this->app->bound('azure.storage'));
        $this->assertTrue($this->app->bound('League\Flysystem\Filesystem'));
    }

    /**
     * Test service provider boot method.
     */
    public function test_service_provider_boot_method()
    {
        $provider = new AzureStorageServiceProvider($this->app);
        
        // Boot should not throw any exceptions
        $this->assertNull($provider->boot());
    }
} 