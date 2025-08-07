<?php

namespace App\Classes;

use Carbon\Carbon;
use GuzzleHttp\Client;
use DateTimeInterface;
use Illuminate\Support\Str;

class AzureStorageAD extends Azure
{
    protected function getStorageClient(?string $storage_name = null): Client {
        if ($storage_name === null) {
            $storage_name = config("azure.storage.account");
        }
        return new Client([
            'base_uri' => 'https://'.$storage_name.".blob.core.windows.net"
        ]);
    }

    protected function storageAccountRequest(string $method, string $url, array $additional_headers, ?string $body = null): string {
        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken('storage'),
            'Date' => $this->getNowForAuthorizationHeader(),
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

        return $this->getStorageClient()->request($method, $url, $options)->getBody()->getContents();
    }

    protected function storageAccountRequestWithoutAuth(string $method, string $url, array $additional_headers, ?string $body = null): string {
        $headers = [
            'Date' => $this->getNowForAuthorizationHeader(),
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

        return $this->getStorageClient()->request($method, $url, $options)->getBody()->getContents();
    }

    protected function storageAccountPut(string $url, array $additional_headers = [], ?string $body = null): string {
        return $this->storageAccountRequest('put', $url, $additional_headers, $body);
    }

    protected function storageAccountGet(string $url, array $additional_headers = []): string {
        return $this->storageAccountRequest('get', $url, $additional_headers);
    }

    /**
     * This method assumes that we are copying to the main storage account that this ccds instances uses.
     * @param $src_account
     * @param $src_blob
     * @param $dest_blob
     */
    public function copy(string $src_account, string $src_blob, string $dest_blob): string {
        $src_url = 'https://'.$src_account.'.blob.core.windows.net/'.$src_blob;
        $headers = [
            'x-ms-copy-source' => $src_url,
        ];
        $storage_name = config("azure.storage.account");
        return $this->storageAccountRequestWithoutAuth(
            'PUT',
            'https://'.$storage_name.".blob.core.windows.net/" . $dest_blob,
            $headers
        );
    }

    public function listBlobs(string $container, ?string $storage_name = null, int $maxResults = 100, ?string $marker = null, ?string $prefix = null): array {
        if ($storage_name === null) {
            $storage_name = config("azure.storage.account");
        }
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
        $container_url = 'https://' . $storage_name . ".blob.core.windows.net/" . $container . "?" . http_build_query($url_params);
        $xml = $this->storageAccountGet($container_url);
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

    public function getBlobProperties(string $container, string $blob, ?string $storage_name): string {
        if ($storage_name === null) {
            $storage_name = config("azure.storage.account");
        }
        $url = 'https://'.$storage_name . '.blob.core.windows.net/'.$container . '/' . $blob;
        return $this->storageAccountRequest('GET', $url, []);
    }

    public function getBlob(string $container, string $blob, ?string $storage_name = null): string {
        if ($storage_name === null) {
            $storage_name = config("azure.storage.account");
        }
        $url = 'https://'.$storage_name . '.blob.core.windows.net/'.$container . '/' . $blob;
        return $this->storageAccountGet($url);
    }

    protected function getNowForAuthorizationHeader(): string {
        $now = Carbon::now(new \DateTimeZone('GMT'));
        return $now->format("D, d M Y H:i:s e");
    }

    /**
     * Generate a SAS token for a blob
     * @param string $container Container name
     * @param string $blob Blob name
     * @param string $permissions Permission string (e.g. 'r' for read, 'w' for write)
     * @param ?DateTimeInterface $start Start time (defaults to now - 1 minute)
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
     * @throws \RuntimeException If account configuration is invalid
     */
    private function buildStringToSign(
        string $permissions,
        DateTimeInterface $start,
        DateTimeInterface $end,
        string $container,
        ?string $blob = null
    ): string {
        $accountName = config('azure.storage.account');
        if (empty($accountName)) {
            throw new \RuntimeException('Storage account name not configured');
        }
        
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
     * @throws \RuntimeException If key decoding fails
     */
    private function signString(string $stringToSign): string
    {
        try {
            // Get storage account key from connection string
            $connectionString = config('azure.storage.connection');
            if (empty($connectionString)) {
                throw new \RuntimeException('Storage connection string not configured');
            }

            // Parse connection string into key-value pairs
            $parts = explode(';', $connectionString);
            $config = [];
            foreach ($parts as $part) {
                if (empty($part)) continue;
                list($key, $value) = explode('=', $part, 2);
                $config[trim($key)] = trim($value);
            }

            if (!isset($config['AccountKey'])) {
                throw new \RuntimeException('Could not find AccountKey in connection string');
            }
            $accountKey = $config['AccountKey'];

            // Log the account key for debugging
            \Log::debug('Using storage account key', [
                'key' => $accountKey
            ]);
            
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
     * Create a new container
     */
    public function createContainer(string $container): string {
        $url = "{$container}?restype=container";
        return $this->storageAccountRequest('PUT', $url, []);
    }

    /**
     * Delete a container
     */
    public function deleteContainer(string $container): string {
        $url = "{$container}?restype=container";
        return $this->storageAccountRequest('DELETE', $url, []);
    }

    /**
     * Create or update a blob with content
     */
    public function putBlob(string $container, string $blob, string $content, string $contentType = 'text/plain'): string {
        $headers = [
            'x-ms-blob-type' => 'BlockBlob',
            'Content-Type' => $contentType
        ];
        return $this->storageAccountRequest(
            'PUT',
            "{$container}/{$blob}",
            $headers,
            $content
        );
    }
}
