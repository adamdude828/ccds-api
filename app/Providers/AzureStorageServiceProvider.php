<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\Filesystem;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;

class AzureStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('azure.storage', function ($app) {
            $config = config('filesystems.disks.azure');
            $client = BlobRestProxy::createBlobService($config['connection'] ?? config('azure.storage.connection'));
            
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container'] ?? config('azure.storage.container')
            );
            return new Filesystem($adapter);
        });
        
        $this->app->bind('League\Flysystem\Filesystem', function ($app) {
            return $app->make('azure.storage');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('azure', function ($app, $config) {
            $client = BlobRestProxy::createBlobService($config['connection']);
            
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container']
            );
            return new Filesystem($adapter);
        });
    }
} 