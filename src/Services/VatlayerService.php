<?php declare(strict_types = 1);

namespace Memo\Vatlayer\Services;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class VatlayerService
{
    private $configService;

    protected const SERVER_URL = 'http://apilayer.net/api/';

    /** @var string The Vatlayer API key, required for all requests. Provided when registering an account. */
    protected $_key;
    /** @var resource */
    protected $_curlHandler;
    /** @var array Response headers received in the most recent API call. */
    protected $_mostRecentResponseHeaders = [];


    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService;
        $this->_key = (string) $configService->get("MemoVatlayer6.config.apiKey");

        //Taken from Postcode.nl Api Client
        if (!extension_loaded('curl'))
        {
            throw new CurlNotLoadedException('Cannot use Vatlayer service, the server needs to have the PHP `cURL` extension installed.');
        }

        $this->_curlHandler = curl_init();
        curl_setopt($this->_curlHandler, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curlHandler, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($this->_curlHandler, CURLOPT_TIMEOUT, 5);
//        curl_setopt($this->_curlHandler, CURLOPT_USERAGENT, $this->_getUserAgent());

        if (isset($_SERVER['HTTP_REFERER']))
        {
            curl_setopt($this->_curlHandler, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
        }
        curl_setopt($this->_curlHandler, CURLOPT_HEADERFUNCTION, function($curl, string $header) {
            $length = strlen($header);

            $headerParts = explode(':', $header, 2);
            // Ignore invalid headers
            if (count($headerParts) < 2)
            {
                return $length;
            }
            [$headerName, $headerValue] = $headerParts;
            $this->_mostRecentResponseHeaders[strtolower(trim($headerName))][] = trim($headerValue);

            return $length;
        });
    }

    public function rateList() {
        return $this->_performApiCall('rate_list');
    }

    public function validate(string $vatId) {
        return $this->_performApiCall('validate', ['vat_number' => $vatId]);
    }

    //Taken from Postcode.nl Api Client
    public function __destruct()
    {
        curl_close($this->_curlHandler);
    }

    //Taken from Postcode.nl Api Client
    protected function _performApiCall(string $path, array $data = []): array
    {
        $data['access_key'] = $this->_key;

        $url = static::SERVER_URL . $path . '?' . http_build_query($data);

        curl_setopt($this->_curlHandler, CURLOPT_URL, $url);

        $this->_mostRecentResponseHeaders = [];
        $response = curl_exec($this->_curlHandler);

        $responseStatusCode = curl_getinfo($this->_curlHandler, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($this->_curlHandler);
        $curlErrorNr = curl_errno($this->_curlHandler);
        if ($curlError !== '')
        {
            throw new \CurlException(vsprintf('Connection error number `%s`: `%s`.', [$curlErrorNr, $curlError]));
        }

        // Parse the response as JSON, will be null if not parsable JSON.
        $jsonResponse = json_decode($response, true);

        switch ($responseStatusCode)
        {
            case 200:
                if (!is_array($jsonResponse))
                {
                    throw new \Exception('Invalid JSON response from the server for request: ' . $url);
                }

                if(array_key_exists("error", $jsonResponse)) {
                    throw new \Exception($jsonResponse['error']['info'], $jsonResponse['error']['code']);
                }

                return $jsonResponse;
            default:
                throw new \Exception(vsprintf('Unexpected server response code `%s`.', [$responseStatusCode]));
        }
    }

}
