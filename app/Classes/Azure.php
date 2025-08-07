<?php

namespace App\Classes;

use GuzzleHttp\Client;
use MicrosoftAzure\Storage\Common\Internal\StorageServiceSettings;
use GuzzleHttp\Exception\GuzzleException;

class Azure {

    protected ?string $token = null;

    protected function getAccessToken(string $token_type = 'management'): string {
        if (!empty($this->token)) {
            return $this->token;
        }
        $client = new Client([
            'base_uri' => 'https://login.microsoft.com/'
        ]);

        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => config("azure.ad.appId"),
            'client_secret' => config("azure.ad.secret"),
            'scope' => 'https://'.$token_type.'.azure.com/.default'
        ];

        $response = $client->post(config('azure.ad.domain') . "/oauth2/v2.0/token", [
            'form_params' => $params
        ]);
        $response_contents = json_decode((string) $response->getBody()->getContents(), true);
        $this->token = $response_contents['access_token'];
        return $this->token;
    }

    protected function getMediaServiceClient(): Client {
        $subscription_id = config("azure.ad.subscription_id");
        $resource_group = config("azure.ad.group");
        return new Client([
            'base_uri' => 'https://management.azure.com/subscriptions/'.$subscription_id.'/resourceGroups/'.$resource_group .
                            '/providers/Microsoft.Media/mediaServices/'
        ]);
    }

    protected function mediaServiceGet(string $url): array {
        return json_decode((string) $this->getMediaServiceClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ])->getBody()->getContents(), true);
    }

    protected function mediaServicePatch(string $url, array $args): array {
        return json_decode((string) $this->getMediaServiceClient()->patch($url, [
            'json' => $args,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ])->getBody()->getContents(), true);
    }

    protected function mediaServicePut(string $url, array $data): array {
        try {
            return json_decode((string) $this->getMediaServiceClient()->put($url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken()
                ]
            ])->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::debug("Media service put exception", [
                'exception_error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function mediaServiceDelete(string $url): array {
        try {
            return json_decode((string) $this->getMediaServiceClient()->delete($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken()
                ]
            ])->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::debug("Media service delete exception", [
                'exception_error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function mediaServicePost(string $url, array $data): array {
        return json_decode((string) $this->getMediaServiceClient()->post($url, [
            'json' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ])->getBody()->getContents(), true);
    }

    public function getAssets(): array {
        $account_name = config("azure.media.name");
        return $this->mediaServiceGet("$account_name/assets?api-version=2020-05-01");
    }

    public function getStreamingEndpoints(): array {
        $account_name = config("azure.media.name");
        return $this->mediaServiceGet("$account_name/streamingEndpoints?api-version=2020-05-01");
    }

    public function editStreamingEndpoint(string $name, array $args): array {
        $account_name = config("azure.media.name");
        return $this->mediaServicePatch(
            "$account_name/streamingEndpoints/$name?api-version=2020-05-01",
            $args
        );
    }

    public function createAsset(string $assetName, string $container): array {
        $account_name = config("azure.media.name");
        $settings = StorageServiceSettings::createFromConnectionString(config('azure.storage.connection'));
        
        return $this->mediaServicePut(
            "$account_name/assets/$assetName?api-version=2020-05-01",
            [
                "properties" => [
                    'storageAccountName' => $settings->getName(),
                    'container' => $container,
                    'description' => 'my asset',
                ]
            ]
        );
    }

    public function deleteAsset(string $assetName): array {
        $account_name = config("azure.media.name");
        return $this->mediaServiceDelete(
            "$account_name/assets/$assetName?api-version=2020-05-01",
        );
    }

    public function createJobPoster(string $input, string $poster_output, string $job): array {
        $account_name = config('azure.media.name');
        return $this->mediaServicePut(
            "$account_name/transforms/".config("azure.media.poster-transform")."/jobs/$job?api-version=2020-05-01",
            [
                "properties" => [
                    "input" => [
                        "@odata.type" => "#Microsoft.Media.JobInputAsset",
                        "assetName" => $input
                    ],
                    "outputs" => [
                        [
                            "@odata.type" => "#Microsoft.Media.JobOutputAsset",
                            "assetName" => $poster_output
                        ]
                    ]
                ]
            ]
        );
    }

    public function createJob(string $input, string $output, string $poster_output, string $job): array {
        $account_name = config('azure.media.name');
        return $this->mediaServicePut(
            "$account_name/transforms/".config("azure.media.transform")."/jobs/$job?api-version=2020-05-01",
            [
                "properties" => [
                    "input" => [
                        "@odata.type" => "#Microsoft.Media.JobInputAsset",
                        "assetName" => $input
                    ],
                    "outputs" => [
                        [
                            "@odata.type" => "#Microsoft.Media.JobOutputAsset",
                            "assetName" => $output
                        ],
                        [
                            "@odata.type" => "#Microsoft.Media.JobOutputAsset",
                            "assetName" => $poster_output
                        ]
                    ]
                ]
            ]
        );
    }

    public function checkJob(string $name): array {
        $account_name = config('azure.media.name');
        return $this->mediaServiceGet(
            "$account_name/transforms/".config("azure.media.transform")."/jobs/$name?api-version=2020-05-01"
        );
    }

    public function createStreamingLocator(string $locatorName, string $outputAsset): array {
        $account_name = config("azure.media.name");
        return $this->mediaServicePut(
            "$account_name/streamingLocators/$locatorName?api-version=2020-05-01",
            [
                'properties' => [
                    'assetName' => $outputAsset,
                    'streamingPolicyName' => 'Predefined_ClearStreamingOnly'
                ]
            ]
        );
    }

    public function createPosterTransform(string $name): array {
        $account_name = config("azure.media.name");
        return $this->mediaServicePut(
            "$account_name/transforms/$name?api-version=2020-05-01",
            [
                'properties' => [
                    "description" => 'capture frame 55',
                    'outputs' => [
                        [
                            "onError" => "StopProcessingJob",
                            "relativePriority" => "Normal",
                            "preset" => [
                                "@odata.type" => "#Microsoft.Media.StandardEncoderPreset",
                                "codecs" => [
                                    [
                                        "@odata.type" => "#Microsoft.Media.PngImage",
                                        "label" => "poster",
                                        "range" => "500",
                                        "start" => "50",
                                        "step" => "100",
                                        "layers" => [
                                            [
                                                "@odata.type" => "#Microsoft.Media.PngLayer",
                                                "height" => "100%",
                                                "width" => "100%"
                                            ]
                                        ],
                                    ]
                                ],
                                "formats" => [
                                    [
                                        "@odata.type" => "#Microsoft.Media.PngFormat",
                                        "filenamePattern" => "{Basename}.png"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function listPaths(string $locatorName): array {
        $account_name = config("azure.media.name");
        return $this->mediaServiceGet(
            "$account_name/streamingLocators/$locatorName/paths?api-version=2020-05-01"
        );
    }

    public function getStorageName(): string {
        return config('azure.storage.name');
    }
    
    public function getStorageKey(): string {
        return config('azure.storage.key');
    }
    
    public function getStorageUrl(): string {
        return config('azure.storage.url');
    }
}
