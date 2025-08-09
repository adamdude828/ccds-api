<?php

namespace App\Services;

use App\Models\CdnPurge;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class FrontDoorService
{
    private Client $client;
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $subscriptionId;
    private string $resourceGroup;
    private string $profileName;
    private string $endpointName;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://management.azure.com/',
            'timeout'  => 30,
        ]);

        $cfg = config('cdn.frontdoor');
        $this->tenantId       = (string) $cfg['tenant_id'];
        $this->clientId       = (string) $cfg['client_id'];
        $this->clientSecret   = (string) $cfg['client_secret'];
        $this->subscriptionId = (string) $cfg['subscription_id'];
        $this->resourceGroup  = (string) $cfg['resource_group'];
        $this->profileName    = (string) $cfg['profile_name'];
        $this->endpointName   = (string) $cfg['endpoint_name'];
    }

    private function getAccessToken(): string
    {
        $http = new Client(['base_uri' => 'https://login.microsoftonline.com/']);
        $response = $http->post($this->tenantId . '/oauth2/v2.0/token', [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => 'https://management.azure.com/.default',
            ],
        ]);
        $json = json_decode($response->getBody()->getContents(), true);
        return $json['access_token'];
    }

    /**
     * Initiate a purge request for the given content paths (e.g. ["/documents/file.pdf"]).
     * Returns the created CdnPurge model.
     */
    public function purge(array $paths): CdnPurge
    {
        $purge = new CdnPurge([
            'paths'         => array_values($paths),
            'status'        => 'pending',
            'provider'      => 'frontdoor',
            'profile_name'  => $this->profileName,
            'endpoint_name' => $this->endpointName,
            'resource_group'=> $this->resourceGroup,
        ]);
        $purge->save();

        return $this->initiate($purge);
    }

    /**
     * Initiate purge for an existing CdnPurge record (queue-friendly).
     */
    public function initiate(CdnPurge $purge): CdnPurge
    {
        $token = $this->getAccessToken();
        $path  = sprintf(
            'subscriptions/%s/resourceGroups/%s/providers/Microsoft.Cdn/profiles/%s/afdEndpoints/%s/purge?api-version=2023-05-01',
            $this->subscriptionId,
            $this->resourceGroup,
            $this->profileName,
            $this->endpointName
        );

        $resp = $this->client->post($path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'contentPaths' => array_values($purge->paths ?? []),
            ],
        ]);

        $status = $resp->getStatusCode();
        $headers = $resp->getHeaders();
        $operationUrl = Arr::get($headers, 'Azure-AsyncOperation.0') ?? Arr::get($headers, 'Location.0');
        $requestId = Arr::get($headers, 'x-ms-request-id.0');

        $purge->status = in_array($status, [200, 202]) ? 'in_progress' : 'failed';
        $purge->operation_url = is_array($operationUrl) ? ($operationUrl[0] ?? null) : $operationUrl;
        $purge->request_id = is_array($requestId) ? ($requestId[0] ?? null) : $requestId;
        $purge->save();

        return $purge;
    }

    /**
     * Query the status of a previously initiated purge using the operation URL.
     * Updates and returns the CdnPurge model.
     */
    public function refreshStatus(CdnPurge $purge): CdnPurge
    {
        if (!$purge->operation_url) {
            return $purge;
        }

        try {
            $token = $this->getAccessToken();
            $resp = $this->client->get($purge->operation_url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
            $json = json_decode($resp->getBody()->getContents(), true);
            $status = $json['status'] ?? 'InProgress';

            if (strcasecmp($status, 'Succeeded') === 0) {
                $purge->status = 'succeeded';
                $purge->completed_at = now();
            } elseif (strcasecmp($status, 'Failed') === 0) {
                $purge->status = 'failed';
                $purge->error_message = $json['error']['message'] ?? null;
                $purge->completed_at = now();
            } else {
                $purge->status = 'in_progress';
            }

            $purge->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to refresh AFD purge status', [
                'purge_id' => $purge->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $purge;
    }
}