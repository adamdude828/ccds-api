<?php

namespace App\Classes;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\BlobProperties;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsResult;
use MicrosoftAzure\Storage\Common\Models\ServiceProperties;
use MicrosoftAzure\Storage\Common\Internal\Resources;
use MicrosoftAzure\Storage\Common\Internal\StorageServiceSettings;
use MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper;
use DateTimeImmutable;

class AzureStorage {

    protected ?string $connectionString = null;
    protected ?BlobRestProxy $blobServiceClient = null;

    public function setConnection(string $connectionString): void {
        $this->connectionString = $connectionString;
        $this->blobServiceClient = BlobRestProxy::createBlobService($connectionString);
    }

    public function getAccountName(): ?string {
        if (empty($this->connectionString)) {
            return null;
        }

        $settings = StorageServiceSettings::createFromConnectionString($this->connectionString);
        return $settings->getName();
    }

    public function createContainer(string $name): void {
        if (!$this->blobServiceClient) {
            throw new \Exception("Blob service client not initialized");
        }
        $this->blobServiceClient->createContainer($name);
    }

    public function downloadFile(string $container, string $blob, string $dest): bool {
        if (!$this->blobServiceClient) {
            throw new \Exception("Tried to download file without specifying connection string");
        }

        try {
            $blobResult = $this->blobServiceClient->getBlob($container, $blob);
            file_put_contents($dest, $blobResult->getContentStream());
        } catch(\Exception $e) {
            \Log::emergency("Failed to transfer file", [
                'container' => $container,
                'blob' => $blob,
                'destination' => $dest,
                'message' => $e->getMessage()
            ]);
            return false;
        }

        return true;
    }

    public function uploadPoster(string $localFile): mixed {
        if (empty($this->connectionString)) {
            throw new \Exception("Tried to upload file without specifying connection string");
        }

        try {
            return Storage::disk('azure-poster')->putFile("", new File($localFile));
        } catch(\Exception $e) {
            \Log::emergency("Failed to transfer file", [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function listFiles(string $container): ListBlobsResult {
        if (!$this->blobServiceClient) {
            throw new \Exception("Cannot list files without setting connection string");
        }

        return $this->blobServiceClient->listBlobs($container);
    }

    public function getSAS(string $container, string $path, string $mode = 'w'): string {
        if (empty($this->connectionString)) {
            throw new \Exception("Cannot get SAS for write without setting connection string");
        }

        $settings = StorageServiceSettings::createFromConnectionString($this->connectionString);
        $accountName = $settings->getName();
        $accountKey = $settings->getKey();

        $startDate = new \DateTime('now');
        $startDate->modify('-1 minute');
        $endDate = new \DateTime('now');
        $endDate->modify('+1 hour');

        $sas = new SharedAccessSignatureHelper(
            $accountName,
            $accountKey
        );

        $permissions = '';
        if (strpos($mode, 'r') !== false) {
            $permissions .= 'r';
        }
        if (strpos($mode, 'w') !== false) {
            $permissions .= 'w';
        }

        return $sas->generateBlobSharedAccessSignatureToken(
            "$container/$path",
            $permissions,
            Resources::RESOURCE_TYPE_BLOB,
            $startDate,
            $endDate
        );
    }

    public function getSASForContainer(string $container, string $mode = 'w'): string {
        if (empty($this->connectionString)) {
            throw new \Exception("Cannot get SAS for write without setting connection string");
        }

        $settings = StorageServiceSettings::createFromConnectionString($this->connectionString);
        $accountName = $settings->getName();
        $accountKey = $settings->getKey();

        $startDate = new \DateTime('now');
        $startDate->modify('-1 minute');
        $endDate = new \DateTime('now');
        $endDate->modify('+1 hour');

        $sas = new SharedAccessSignatureHelper(
            $accountName,
            $accountKey
        );

        $permissions = '';
        if (strpos($mode, 'r') !== false) {
            $permissions .= 'r';
        }
        if (strpos($mode, 'w') !== false) {
            $permissions .= 'w';
        }

        return $sas->generateContainerSharedAccessSignatureToken(
            $container,
            $permissions,
            Resources::RESOURCE_TYPE_CONTAINER,
            $startDate,
            $endDate
        );
    }
}
