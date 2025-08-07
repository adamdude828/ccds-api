<?php

use Tests\TestCase;
use App\Classes\AzureStorageSAS;
use Carbon\Carbon;
use GuzzleHttp\Client;

uses(TestCase::class);

beforeEach(function () {
    // Initialize Azure Storage SAS
    $this->azureStorage = new AzureStorageSAS();
    
    // Get storage account name from config
    $this->storageAccount = config('azure.storage.account');
    $this->baseUrl = "https://{$this->storageAccount}.blob.core.windows.net";
    
    // Use an existing container from config
    $this->testContainer = config('azure.storage.poster');
    
    // Create a unique blob name for testing
    $this->testBlob = 'test-blob-' . uniqid() . '.txt';
    
    try {
        // Create test blob with content in the existing container
        $this->azureStorage->putBlob(
            $this->testContainer,
            $this->testBlob,
            "Test content"
        );
    } catch (\Exception $e) {
        test()->markTestSkipped('Could not create test blob: ' . $e->getMessage());
    }
});

afterEach(function () {
    // Clean up the test blob (but leave the container intact)
    try {
        $this->azureStorage->deleteBlob($this->testContainer, $this->testBlob);
    } catch (\Exception $e) {
        error_log('Failed to cleanup test blob: ' . $e->getMessage());
    }
});

test('can read blob with sas token', function () {
    // Generate a read-only SAS token for the blob
    $sasToken = $this->azureStorage->generateBlobSAS(
        $this->testContainer,
        $this->testBlob,
        'r', // read-only permission
        Carbon::now()->subMinutes(5),
        Carbon::now()->addHour()
    );

    // Create a client and try to read the blob
    $client = new Client();
    $url = "{$this->baseUrl}/{$this->testContainer}/{$this->testBlob}?" . $sasToken;
    
    // Debug info
    error_log("Attempting to access URL: " . $url);
    
    $response = $client->get($url);
    expect($response->getStatusCode())->toBe(200);
    expect((string)$response->getBody())->toBe("Test content");
}); 