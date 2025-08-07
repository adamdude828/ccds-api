<?php

namespace App\Services\Mediakind;

use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MicrosoftAzure\Storage\Common\Internal\StorageServiceSettings;

class Mediakind
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config("mediakind.base_url");
    }


    /**
     * The method listPaths returns the set of valid streaming paths for a given Streaming Locator. A distinct set of
     * paths is returned for each type of DRM (Digital Rights Management) configured, per the 'EnabledProtocols' property
     * of the streaming policy.
     *
     * An API call is made to Mediakind with the 'locatorName' in a HTTP POST request to get the list of streaming paths.
     *
     * @param string $locatorName The name of the Streaming Locator
     * @return array The response from MediaKind for the listPaths request
     * @throws Exception If there is an error in the API call
     */
    public function listPaths($locatorName){
        $project_name = config("mediakind.project_name");
        $url = "/ams/$project_name/streamingLocators/$locatorName/listPaths";

        Log::info('URL being used: ' . $url);

        $client = new \GuzzleHttp\Client();
        $full_url = $this->baseUrl . $url;

        try {
            /**
             * @var Response $response
             */
            $response = $client->request('POST', $full_url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Mkio-Token' => config('mediakind.key')
                ],
                'body' => ''
            ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            Log::error('Guzzle error: '. $e->getMessage());
        }

        return json_decode($response->getBody(), true);
    }


    /**
     * Creates a Streaming Locator in the Mediakind cloud service.
     *
     * The Streaming Locator is created with the given locator name and output asset.
     * It makes an API call to MediaKind with these details in a HTTP PUT request.
     *
     * @param string $locatorName The name of the Streaming Locator to create
     * @param string $outputAsset The name of the asset for the Streaming Locator output
     *
     * @return array The response from MediaKind for the create Streaming Locator request
     * @throws Exception If there is an error in the API call
     */
    public function createStreamingLocator($locatorName, $outputAsset) {
        $project_name = config("mediakind.project_name");
        $url = "/ams/$project_name/streamingLocators/$locatorName";
        $streaming_locator_properties = [
            'properties'=>[
                'assetName'=>$outputAsset,
                'streamingPolicyName'=>'Predefined_ClearStreamingOnly'
            ]
        ];
        return $this->put(
            $url,
            $streaming_locator_properties
        );
    }


    /**
     * This method checks a job in the MediaKind cloud service.
     *
     * The job status is retrieved by making an API call to MediaKind with
     * the job identifier as part of a HTTP GET request.
     *
     * @param string $job The job identifier
     * @return array The response from MediaKind for the check job request
     * @throws Exception If there is an error in the API call
     */
    public function checkJob($job)
    {
        $project_name = config("mediakind.project_name");
        $transform_name = config('mediakind.transform');
        $url = "/ams/$project_name/transforms/$transform_name/jobs/$job";
        return $this->get($url);
    }

     /**
     * This method creates a job in the MediaKind cloud service.
     *
     * The job is created with input, output, and poster output details.
     * Also, it takes the transfomation policy through the transform
     * config available. Makes an API call to MediaKind with these
     * details in a HTTP PUT request.
     *
     * @param string $input The input asset name for the job
     * @param string $output The output asset name for the job
     * @param string $job The job identifier
     * @return array The response from MediaKind for the create job request
     * @throws Exception If there is an error in the API call
     */
    public function createJob($input, $output, $job, $file) {
        $project_name = config("mediakind.project_name");
        $transform_name = config('mediakind.transform');
        $url = "/ams/$project_name/transforms/$transform_name/jobs/$job";
        $job_params = [
            "properties" => [
                "input"=>[
                    "@odata.type" => "#Microsoft.Media.JobInputAsset",
                    "assetName" => $input,
                    "files" => [
                        $file
                    ]
                ],
                "outputs"=>[
                    [
                        "@odata.type" => "#Microsoft.Media.JobOutputAsset",
                        "assetName" => $output
                    ],
                ]
            ]
        ];

        return $this->put(
            $url,
            $job_params
        );
    }


    /**
     * This method fetches a job's status from the MediaKind cloud service.
     *
     * The job status is retrieved by making an API call to MediaKind with
     * the job identifier as part of a HTTP GET request.
     *
     * @param string $job The job identifier
     * @return array The response from MediaKind for the job status request
     * @throws Exception If there is an error in the API call
     */
    public function jobStatus($job) {
        $project_name = config("mediakind.project_name");
        $transform_name = config('mediakind.transform');
        $url = "/ams/$project_name/transforms/$transform_name/jobs/$job";
        return $this->get($url);
    }

    /**
     * Create asset as part of the video creation process.
     * @param string $asset_name
     * @param string $container_name
     * @return mixed
     * @throws Exception
     */
    public function createAsset(string $asset_name, string $container_name) {
       $project_name = config("mediakind.project_name");
       $url = "/ams/$project_name/assets/$asset_name";
       $settings = StorageServiceSettings::createFromConnectionString(config('azure.storage.connection'));
       $asset_params = [
           'properties' => [
               'container' => $container_name,
               'storageAccountName' => $settings->getName()
           ]
       ];


       return $this->put($url, $asset_params);
    }


    /**
     * List all assets.
     * Not used currently, built to test the api. I figure there is not harm in keeping it.
     * @return mixed
     * @throws Exception
     */
    public function listAssets()
    {
        $project_name = config("mediakind.project_name");
        $url = "/ams/$project_name/assets";

        return $this->get($url);
    }

    /**
     * Update the transform used by the project
     * @return mixed
     * @throws Exception
     */
    public function createOrUpdateTransform()
    {
        $project_name = config('mediakind.project_name');
        $transform_properties = config("mediakind.transform_properties");
        $transform_name = config("mediakind.transform");
        $url = "/ams/$project_name/transforms/$transform_name";
        return $this->put($url, $transform_properties);
    }

    /**
     * Report error if received
     * @param $api_response
     * @return mixed
     * @throws Exception
     */
    protected function reportError($api_response)
    {
        $error_message = "Error returned from mediakind api.";
        Log::error($error_message, $api_response);
        throw new Exception($error_message);
    }

    /**
     * Perform an HTTP GET request to a given endpoint with optional data.
     *
     * @param string $endpoint The endpoint to send the GET request to.
     * @param array $data Optional data to include in the request.
     * @return mixed The result of the GET request, typically in JSON format.
     * @throws Exception
     */
    protected function get(string $endpoint, array $data = [])
    {
        $response = Http::withHeaders(['X-Mkio-Token' => config('mediakind.key')])
            ->get($this->baseUrl . '/' . ltrim($endpoint, '/'), $data);

        $result = $response->json();

        if ($response->failed()) {
            $this->reportError($result ?? ['error' => ['detail' => 'HTTP request failed', 'status' => $response->status()]]);
        }

        return $result;
    }



    /**
     * Perform an HTTP POST request to a given endpoint with optional data.
     *
     * @param string $endpoint The endpoint to send the POST request to.
     * @param array $data Optional data to include in the request.
     * @return mixed The result of the POST request, typically in JSON format.
     * @throws Exception If the HTTP request fails.
     */
    protected function post($endpoint, array $data = [])
    {
        $response = Http::withHeaders(['X-Mkio-Token' => config('mediakind.key')])
            ->post($this->baseUrl . '/' . ltrim($endpoint, '/'), $data);

        Log::debug('HTTP POST request sent to ' . $this->baseUrl . '/' . ltrim($endpoint, '/'));
        Log::debug('HTTP POST request data: ', $data);

        $result = $response->json();

        if ($response->failed()) {
            $this->reportError($result ?? ['error' => ['detail' => 'HTTP request failed', 'status' => $response->status()]]);
        } else {
            Log::debug('HTTP POST request response: ', $result);
        }

        return $result;
    }

    /**
     * Perform an HTTP PUT request to a given endpoint with optional data.
     *
     * @param string $endpoint The endpoint to send the PUT request to.
     * @param array $data Optional data to include in the request.
     * @return mixed The result of the PUT request, typically in JSON format.
     * @throws Exception
     */
    protected function put($endpoint, array $data = [])
    {
        $response = Http::withHeaders(['X-Mkio-Token' => config('mediakind.key')])
            ->put($this->baseUrl . '/' . ltrim($endpoint, '/'), $data);

        $result = $response->json();

        if ($response->failed()) {
            $this->reportError($result ?? ['error' => ['detail' => 'HTTP request failed', 'status' => $response->status()]]);
        }

        return $result;
    }


    /**
     * Perform an HTTP DELETE request to a given endpoint with optional data.
     *
     * @param string $endpoint The endpoint to send the DELETE request to.
     * @param array $data Optional data to include in the request.
     * @return mixed The result of the DELETE request, typically in JSON format.
     * @throws Exception
     */
    protected function delete($endpoint, array $data = [])
    {
        $response = Http::withHeaders(['X-Mkio-Token' => config('mediakind.key')])
            ->delete($this->baseUrl . '/' . ltrim($endpoint, '/'), $data);

        $result = $response->json();

        if ($response->failed()) {
            $this->reportError($result ?? ['error' => ['detail' => 'HTTP request failed', 'status' => $response->status()]]);
        }

        return $result;
    }
}
