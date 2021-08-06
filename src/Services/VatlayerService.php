<?php declare(strict_types=1);

namespace Memo\Vatlayer\Services;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class VatlayerService
{
    protected const SERVER_URL = 'http://apilayer.net/api/';

    /** @var string|null The Vatlayer API key, required for all requests. Provided when registering an account. */
    protected $apiKey = null;

    /** @var resource */
    protected $curlHandler;

    /** @var array Response headers received in the most recent API call. */
    protected $mostRecentResponseHeaders = [];

    public function __construct(SystemConfigService $configService)
    {
        $this->setApiKey((string)$configService->get("MemoVatlayer6.config.apiKey"));

        $this->initCurlHandler();
    }

    public function rateList()
    {
        return $this->performApiCall('rate_list');
    }

    public function validate(string $vatId)
    {
        return $this->performApiCall('validate', ['vat_number' => $vatId]);
    }

    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    // API functions
    public function __destruct()
    {
        curl_close($this->curlHandler);
    }

    protected function initCurlHandler()
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('Cannot use Vatlayer service, the server needs to have the PHP `cURL` extension installed.');
        }

        $this->curlHandler = curl_init();
        curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandler, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($this->curlHandler, CURLOPT_TIMEOUT, 5);

        if (isset($_SERVER['HTTP_REFERER'])) {
            curl_setopt($this->curlHandler, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
        }

        curl_setopt($this->curlHandler, CURLOPT_HEADERFUNCTION, function ($curl, string $header) {
            $length = strlen($header);

            $headerParts = explode(':', $header, 2);
            // Ignore invalid headers
            if (count($headerParts) < 2) {
                return $length;
            }

            [$headerName, $headerValue] = $headerParts;
            $this->mostRecentResponseHeaders[strtolower(trim($headerName))][] = trim($headerValue);

            return $length;
        });
    }

    protected function performApiCall(string $path, array $data = []): array
    {
        $data['access_key'] = $this->apiKey;

        $url = static::SERVER_URL . $path . '?' . http_build_query($data);

        curl_setopt($this->curlHandler, CURLOPT_URL, $url);

        $this->mostRecentResponseHeaders = [];
        $response = curl_exec($this->curlHandler);

        $responseStatusCode = curl_getinfo($this->curlHandler, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($this->curlHandler);
        $curlErrorNr = curl_errno($this->curlHandler);
        if ($curlError !== '') {
            throw new \Exception(vsprintf('Connection error number `%s`: `%s`.', [$curlErrorNr, $curlError]));
        }

        // Parse the response as JSON, will be null if not parsable JSON.
        $jsonResponse = json_decode($response, true);

        switch ($responseStatusCode) {
            case 200:
                if (!is_array($jsonResponse)) {
                    throw new \Exception('Invalid JSON response from the server for request: ' . $url);
                }

                if (array_key_exists("error", $jsonResponse)) {
                    throw new \Exception($jsonResponse['error']['info'], $jsonResponse['error']['code']);
                }

                return $jsonResponse;
            default:
                throw new \Exception(vsprintf('Unexpected server response code `%s`.', [$responseStatusCode]));
        }
    }

}
