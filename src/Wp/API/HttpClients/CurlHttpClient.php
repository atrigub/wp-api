<?php

namespace Wp\API\HttpClients;

use Wp\API\Exception\WpApiException;


/**
 * Class CurlHttpClient base on FB curl client
 *
 * @package Wp\API\HttpClients
 */
class CurlHttpClient
{
    /**
     * @var array The headers to be sent with the request
     */
    protected $requestHeaders = array();

    /**
     * @var array The headers received from the response
     */
    protected $responseHeaders = array();

    /**
     * @var int
     */
    protected $responseHttpStatusCode = 0;

    /**
     * @var string
     */
    protected $curlErrorMessage = '';

    /**
     * @var int
     */
    protected $curlErrorCode = 0;

    /**
     * @var string|boolean
     */
    protected $rawResponse;

    /**
     * @var CurlWrapper
     */
    protected $curlWrapper;

    /**
     * @const Curl Version which is unaffected by the proxy header length error.
     */
    const CURL_PROXY_QUIRK_VER = 0x071E00;

    /**
     * @const "Connection Established" header text
     */
    const CONNECTION_ESTABLISHED = "HTTP/1.0 200 Connection established\r\n\r\n";

    /**
     * init
     */
    public function __construct()
    {
        $this->curlWrapper = new CurlWrapper();
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addRequestHeader($key, $value)
    {
        $this->requestHeaders[$key] = $value;
    }

    /**
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     *
     * @return int
     */
    public function getResponseHttpStatusCode()
    {
        return $this->responseHttpStatusCode;
    }

    /**
     * @param string $url The endpoint to send the request to
     * @param string $method The request method
     * @param array  $parameters The key value pairs to be sent in the body
     *
     * @return string Raw response from the server
     *
     */
    public function send($url, $method = 'GET', $parameters = array())
    {
        $this->openConnection($url, $method, $parameters);
        $this->tryToSendRequest();

        if ($this->curlErrorCode) {
            throw new WpApiException(array(
                'error' => ['message' => 'Response is empty'],
                'error_code' => $this->curlErrorCode
            ));
        }

        // Separate the raw headers from the raw body
        list($rawHeaders, $rawBody) = $this->extractResponseHeadersAndBody();

        $this->responseHeaders = self::headersToArray($rawHeaders);

        $this->closeConnection();

        return $rawBody;
    }

    /**
     * Opens a new curl connection
     *
     * @param string $url The endpoint to send the request to
     * @param string $method The request method
     * @param array  $parameters The key value pairs to be sent in the body
     */
    public function openConnection($url, $method = 'GET', array $parameters = array())
    {
        $parameters = $this->flattenCurlParams($parameters);
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
        );

        if ($method !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = !$this->paramsHaveFile($parameters)
                ? http_build_query($parameters, null, '&') : $parameters;
        }

        if ($method === 'DELETE' || $method === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        if (count($this->requestHeaders) > 0) {
            $options[CURLOPT_HTTPHEADER] = $this->compileRequestHeaders();
        }

        $this->curlWrapper->init();
        $this->curlWrapper->setOptArray($options);
    }

    /**
     * Closes an existing curl connection
     */
    public function closeConnection()
    {
        $this->curlWrapper->close();
    }

    /**
     * Try to send the request
     */
    public function tryToSendRequest()
    {
        $this->sendRequest();
        $this->curlErrorMessage = $this->curlWrapper->error();
        $this->curlErrorCode = $this->curlWrapper->errNo();
        $this->responseHttpStatusCode = $this->curlWrapper->getInfo(CURLINFO_HTTP_CODE);
    }

    /**
     * Send the request and get the raw response from curl
     */
    public function sendRequest()
    {
        $this->rawResponse = $this->curlWrapper->exec();
    }

    /**
     * Compiles the request headers into a curl-friendly format
     *
     * @return array
     */
    public function compileRequestHeaders()
    {
        $return = array();

        foreach ($this->requestHeaders as $key => $value) {
            $return[] = $key . ': ' . $value;
        }

        return $return;
    }

    /**
     * Extracts the headers and the body into a two-part array
     *
     * @return array
     */
    public function extractResponseHeadersAndBody()
    {
        $headerSize = self::getHeaderSize();

        $rawHeaders = mb_substr($this->rawResponse, 0, $headerSize);
        $rawBody = mb_substr($this->rawResponse, $headerSize);

        return array(trim($rawHeaders), trim($rawBody));
    }

    /**
     * Converts raw header responses into an array
     *
     * @param string $rawHeaders
     *
     * @return array
     */
    public static function headersToArray($rawHeaders)
    {
        $headers = array();

        $rawHeaders = str_replace("\r\n", "\n", $rawHeaders);

        $headerCollection = explode("\n\n", trim($rawHeaders));

        $rawHeader = array_pop($headerCollection);

        $headerComponents = explode("\n", $rawHeader);
        foreach ($headerComponents as $line) {
            if (strpos($line, ': ') === false) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * @param array $params
     * @param null  $prefix
     *
     * @return array
     */
    private function flattenCurlParams(array $params, $prefix = null)
    {
        $return = array();
        foreach ($params as $idx => $value) {
            if (is_array($value)) {
                $return = array_merge($return, $this->flattenCurlParams($value, $prefix ? $prefix . '[' . $idx . ']' : $idx));
            } else {
                if ($prefix) {
                    $return[$prefix . '[' . $idx . ']'] = $value;
                } else {
                    $return[$idx] = $value;
                }
            }
        }

        return $return;
    }

    /**
     * Return proper header size
     *
     * @return integer
     */
    private function getHeaderSize()
    {
        $headerSize = $this->curlWrapper->getinfo(CURLINFO_HEADER_SIZE);

        if ( $this->needsCurlProxyFix() ) {
            // Additional way to calculate the request body size.
            if (preg_match('/Content-Length: (\d+)/', $this->rawResponse, $m)) {
                $headerSize = mb_strlen($this->rawResponse) - $m[1];
            } elseif (stripos($this->rawResponse, self::CONNECTION_ESTABLISHED) !== false) {
                $headerSize += mb_strlen(self::CONNECTION_ESTABLISHED);
            }
        }

        return $headerSize;
    }

    /**
     * Detect versions of Curl which report incorrect header lengths when
     * using Proxies.
     *
     * @return boolean
     */
    private function needsCurlProxyFix()
    {
        $ver = $this->curlWrapper->version();
        $version = $ver['version_number'];

        return $version < self::CURL_PROXY_QUIRK_VER;
    }

    /**
     * Detect if the params have a file to upload.
     *
     * @param array $params
     *
     * @return boolean
     */
    private function paramsHaveFile(array $params)
    {
        foreach ($params as $value) {
            if ($value instanceof \CURLFile) {
                return true;
            }
        }

        return false;
    }
}