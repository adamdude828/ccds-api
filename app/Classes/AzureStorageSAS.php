<?php

namespace App\Classes;

use Carbon\Carbon;
use GuzzleHttp\Client;
use DateTimeInterface;
use Illuminate\Support\Str;

class AzureStorageSAS
{
    /**
     * Get the storage account name from configuration
     */
    protected function getStorageAccountName(): string
    {
        $accountName = config('azure.storage.account');
        if (empty($accountName)) {
            throw new \RuntimeException('Storage account name not configured');
        }
        return $accountName;
    }

    /**
     * Get the storage account key from configuration
     */
    protected function getStorageAccountKey(): string
    {
        $accountKey = config('azure.storage.account_key');
        if (empty($accountKey)) {
            throw new \RuntimeException('Storage account key not configured');
        }
        return $accountKey;
    }

    /**
     * Get a storage client for the specified storage account
     */
    protected function getStorageClient(?string $storage_name = null): Client
    {
        if ($storage_name === null) {
            $storage_name = $this->getStorageAccountName();
        }
        return new Client([
            'base_uri' => 'https://' . $storage_name . '.blob.core.windows.net'
        ]);
    }

    /**
     * Make a request to the storage account using SAS token
     */
    protected function storageAccountRequestWithSAS(string $method, string $url, string $sasToken, array $additional_headers = [], ?string $body = null): string
    {
        $headers = [
            'x-ms-date' => $this->getNowForAuthorizationHeader(),
            'x-ms-version' => '2022-11-02'
        ];
        $headers = array_merge($headers, $additional_headers);

        $options = ['headers' => $headers];
        if ($body !== null) {
            $options['body'] = $body;
            if (!isset($headers['Content-Length'])) {
                $options['headers']['Content-Length'] = strlen($body);
            }
        }

        // Append SAS token to URL if it doesn't already have query parameters
        if (strpos($url, '?') === false) {
            $url .= '?' . $sasToken;
        } else {
            $url .= '&' . $sasToken;
        }

        return $this->getStorageClient()->request($method, $url, $options)->getBody()->getContents();
    }

    /**
     * Get the current time formatted for authorization headers
     */
    protected function getNowForAuthorizationHeader(): string
    {
        $now = Carbon::now(new \DateTimeZone('GMT'));
        return $now->format('D, d M Y H:i:s e');
    }

    /**
     * Generate a SAS token for a blob
     * @param string $container Container name
     * @param string $blob Blob name
     * @param string $permissions Permission string (e.g. 'r' for read, 'w' for write)
     * @param ?DateTimeInterface $start Start time (defaults to now - 5 minutes)
     * @param ?DateTimeInterface $end End time (defaults to now + 1 hour)
     * @return string The SAS token query string
     * @throws \InvalidArgumentException If permissions are invalid
     */
    public function generateBlobSAS(string $container, string $blob, string $permissions = 'r', ?DateTimeInterface $start = null, ?DateTimeInterface $end = null): string
    {
        // Validate permissions for blobs
        $validBlobPermissions = ['r', 'a', 'c', 'w', 'd'];
        $invalidChars = array_diff(str_split($permissions), $validBlobPermissions);
        if (!empty($invalidChars)) {
            throw new \InvalidArgumentException(
                'Invalid blob permissions. Valid permissions are: ' . implode(', ', $validBlobPermissions)
            );
        }

        if (empty($container) || empty($blob)) {
            throw new \InvalidArgumentException('Container and blob names cannot be empty');
        }

        $start = $start ?? Carbon::now()->subMinutes(5);
        $end = $end ?? Carbon::now()->addHour();

        // Validate time range
        if ($end <= $start) {
            throw new \InvalidArgumentException('End time must be after start time');
        }

        return $this->buildSasQueryString($permissions, $start, $end, $container, $blob);
    }

    /**
     * Generate a SAS token for a container
     * @param string $container Container name
     * @param string $permissions Permission string (e.g. 'r' for read, 'l' for list)
     * @param ?DateTimeInterface $start Start time
     * @param ?DateTimeInterface $end End time
     * @return string The SAS token query string
     * @throws \InvalidArgumentException If permissions are invalid
     */
    public function generateContainerSAS(string $container, string $permissions = 'r', ?DateTimeInterface $start = null, ?DateTimeInterface $end = null): string
    {
        // Validate permissions for containers
        $validContainerPermissions = ['r', 'a', 'c', 'w', 'd', 'l'];
        $invalidChars = array_diff(str_split($permissions), $validContainerPermissions);
        if (!empty($invalidChars)) {
            throw new \InvalidArgumentException(
                'Invalid container permissions. Valid permissions are: ' . implode(', ', $validContainerPermissions)
            );
        }

        if (empty($container)) {
            throw new \InvalidArgumentException('Container name cannot be empty');
        }

        $start = $start ?? Carbon::now()->subMinutes(5);
        $end = $end ?? Carbon::now()->addHour();

        // Validate time range
        if ($end <= $start) {
            throw new \InvalidArgumentException('End time must be after start time');
        }

        return $this->buildSasQueryString($permissions, $start, $end, $container);
    }

    /**
     * Build the string to sign for SAS token generation
     * Format documented at: https://learn.microsoft.com/en-us/rest/api/storageservices/create-service-sas
     */
    private function buildStringToSign(
        string $permissions,
        DateTimeInterface $start,
        DateTimeInterface $end,
        string $container,
        ?string $blob = null
    ): string {
        $accountName = $this->getStorageAccountName();
        
        // The canonicalized resource should start with a forward slash
        $canonicalizedResource = "/blob/$accountName/$container";
        if ($blob) {
            $canonicalizedResource .= "/$blob";
        }

        // Use a documented API version
        $apiVersion = '2020-12-06';

        // Service SAS string-to-sign format
        $fields = [
            $permissions,                           // signedPermissions
            $start->format('Y-m-d\TH:i:s\Z'),      // signedStart
            $end->format('Y-m-d\TH:i:s\Z'),        // signedExpiry
            $canonicalizedResource,                 // canonicalizedResource
            '',                                     // signedIdentifier
            '',                                     // signedIP
            'https',                               // signedProtocol
            $apiVersion,                           // signedVersion
            $blob ? 'b' : 'c',                     // signedResource (b=blob, c=container)
            '',                                     // signedSnapshotTime
            '',                                     // signedEncryptionScope
            '',                                     // rscc (Cache-Control)
            '',                                     // rscd (Content-Disposition)
            '',                                     // rsce (Content-Encoding)
            '',                                     // rscl (Content-Language)
            ''                                      // rsct (Content-Type)
        ];

        // Ensure we have exactly 15 newlines (16 fields)
        $stringToSign = implode("\n", $fields);

        // Only log non-sensitive information
        \Log::debug('Building SAS string-to-sign', [
            'resource_type' => $blob ? 'blob' : 'container',
            'container' => $container,
            'field_count' => count($fields),
            'api_version' => $apiVersion,
            'start_time' => $start->format('Y-m-d\TH:i:s\Z'),
            'end_time' => $end->format('Y-m-d\TH:i:s\Z')
        ]);

        return $stringToSign;
    }

    /**
     * Sign the string using the storage account key
     */
    private function signString(string $stringToSign): string
    {
        try {
            // Get storage account key directly from config
            $accountKey = $this->getStorageAccountKey();
            
            // Decode the base64 key
            $key = base64_decode($accountKey, true);
            if ($key === false) {
                throw new \RuntimeException('Invalid account key format');
            }
            
            // Create HMAC signature using raw binary mode for both input and output
            $signature = hash_hmac('sha256', utf8_encode($stringToSign), $key, true);
            if ($signature === false) {
                throw new \RuntimeException('Failed to generate HMAC signature');
            }

            $encodedSignature = base64_encode($signature);

            // Log only non-sensitive information
            \Log::debug('Generated SAS signature', [
                'signature_length' => strlen($encodedSignature)
            ]);

            return $encodedSignature;
        } catch (\Exception $e) {
            \Log::error('Failed to generate SAS signature', [
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Failed to generate SAS signature: ' . $e->getMessage());
        }
    }

    /**
     * Build the final SAS query string
     */
    private function buildSasQueryString(
        string $permissions,
        DateTimeInterface $start,
        DateTimeInterface $end,
        string $container,
        ?string $blob = null
    ): string {
        try {
            $stringToSign = $this->buildStringToSign($permissions, $start, $end, $container, $blob);
            $signature = $this->signString($stringToSign);

            // Sort query parameters lexicographically
            $queryParams = [
                'sp' => $permissions,                                 // Signed permissions
                'sr' => $blob ? 'b' : 'c',                           // Signed resource (b=blob, c=container)
                'st' => $start->format('Y-m-d\TH:i:s\Z'),           // Start time
                'se' => $end->format('Y-m-d\TH:i:s\Z'),             // Expiry time
                'spr' => 'https',                                    // Protocol
                'sv' => '2020-12-06',                               // Signed version
                'sig' => $signature                                  // Signature
            ];
            ksort($queryParams);

            return http_build_query($queryParams);
        } catch (\Exception $e) {
            \Log::error('Failed to build SAS query string', [
                'error' => $e->getMessage(),
                'container' => $container,
                'resource_type' => $blob ? 'blob' : 'container'
            ]);
            throw new \RuntimeException('Failed to build SAS query string: ' . $e->getMessage());
        }
    }

    /**
     * Create a new container using SAS token
     */
    public function createContainer(string $container): string
    {
        $sasToken = $this->generateContainerSAS($container, 'c');
        $url = "{$container}?restype=container";
        return $this->storageAccountRequestWithSAS('PUT', $url, $sasToken);
    }

    /**
     * Delete a container using SAS token
     */
    public function deleteContainer(string $container): string
    {
        $sasToken = $this->generateContainerSAS($container, 'd');
        $url = "{$container}?restype=container";
        return $this->storageAccountRequestWithSAS('DELETE', $url, $sasToken);
    }

    /**
     * Create or update a blob with content using SAS token
     */
    public function putBlob(string $container, string $blob, string $content, string $contentType = 'text/plain'): string
    {
        $sasToken = $this->generateBlobSAS($container, $blob, 'w');
        $headers = [
            'x-ms-blob-type' => 'BlockBlob',
            'Content-Type' => $contentType
        ];
        return $this->storageAccountRequestWithSAS(
            'PUT',
            "{$container}/{$blob}",
            $sasToken,
            $headers,
            $content
        );
    }

    /**
     * Get a blob using SAS token
     */
    public function getBlob(string $container, string $blob): string
    {
        $sasToken = $this->generateBlobSAS($container, $blob, 'r');
        return $this->storageAccountRequestWithSAS('GET', "{$container}/{$blob}", $sasToken);
    }

    /**
     * Delete a blob using SAS token
     */
    public function deleteBlob(string $container, string $blob): string
    {
        $sasToken = $this->generateBlobSAS($container, $blob, 'd');
        return $this->storageAccountRequestWithSAS('DELETE', "{$container}/{$blob}", $sasToken);
    }

    /**
     * List blobs in a container using SAS token
     */
    public function listBlobs(string $container, int $maxResults = 100, ?string $marker = null, ?string $prefix = null): array
    {
        $sasToken = $this->generateContainerSAS($container, 'l');
        
        $url_params = [];
        $url_params['restype'] = 'container';
        $url_params['comp'] = 'list';
        $url_params['maxresults'] = $maxResults;
        if (!is_null($marker)) {
            $url_params['marker'] = $marker;
        }
        if (!is_null($prefix)) {
            $url_params['prefix'] = $prefix;
        }
        
        $container_url = "{$container}?" . http_build_query($url_params);
        $xml = $this->storageAccountRequestWithSAS('GET', $container_url, $sasToken);
        
        $parsed = new \SimpleXMLElement($xml);
        $results = [];
        $results['blobs'] = [];
        foreach($parsed->Blobs->Blob as $blob) {
            $results['blobs'][] = [
                'name' => (string) $blob->Name,
                'properties' => (array) $blob->Properties
            ];
        }
        $results['nextMarker'] = (string) $parsed->NextMarker;
        
        return $results;
    }

    /**
     * Get a SAS token for a blob (backward compatibility with AzureStorage class)
     * @param string $container Container name
     * @param string $path Blob name
     * @param string $mode Permission mode ('r' for read, 'w' for write, 'rw' for both)
     * @return string The SAS token
     */
    public function getSAS(string $container, string $path, string $mode = 'w'): string
    {
        $permissions = '';
        if (strpos($mode, 'r') !== false) {
            $permissions .= 'r';
        }
        if (strpos($mode, 'w') !== false) {
            $permissions .= 'w';
        }
        if (strpos($mode, 'd') !== false) {
            $permissions .= 'd';
        }
        
        return $this->generateBlobSAS($container, $path, $permissions);
    }

    /**
     * Get a SAS token for a container (backward compatibility with AzureStorage class)
     * @param string $container Container name
     * @param string $mode Permission mode ('r' for read, 'w' for write, 'rw' for both)
     * @return string The SAS token
     */
    public function getSASForContainer(string $container, string $mode = 'w'): string
    {
        $permissions = '';
        if (strpos($mode, 'r') !== false) {
            $permissions .= 'r';
        }
        if (strpos($mode, 'w') !== false) {
            $permissions .= 'w';
        }
        if (strpos($mode, 'd') !== false) {
            $permissions .= 'd';
        }
        if (strpos($mode, 'l') !== false) {
            $permissions .= 'l';
        }
        
        return $this->generateContainerSAS($container, $permissions);
    }
} 