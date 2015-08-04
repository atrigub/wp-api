<?php

namespace Wp\API\HttpClients;


use Wp\API\Exception\WpApiException;

class StreamHttpClient
{
    /**
     * @var array
     */
    protected $requestHeaders = array();

    /**
     * @var array
     */
    protected $responseHeaders = array();

    /**
     * @var int
     */
    protected $responseHttpStatusCode = 0;


    /**
     * @param string $key
     * @param string $value
     */
    public function addRequestHeader($key, $value)
    {
        $this->requestHeaders[$key] = $value;
    }

    /**
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * @return int
     */
    public function getResponseHttpStatusCode()
    {
        return $this->responseHttpStatusCode;
    }

    /**
     * Sends a request to the server
     *
     * @param string $url
     * @param string $method
     * @param array  $params
     *
     * @return string
     *
     * @throws WpApiException
     */
    public function send($url, $method = 'GET', array $params = array())
    {
        $options = array(
            'http' => array(
                'method' => $method,
                'timeout' => 60,
                'ignore_errors' => true
            )
        );

        if ($params !== array()) {
            $this->addRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            $options['http']['content'] = http_build_query($params, null, '&');
        }

        $options['http']['header'] = $this->buildHeader();

        $stream = stream_context_create($options);

        $rawResponse = file_get_contents($url, false, $stream);
        $rawHeaders = $http_response_header;

        if ($rawResponse === false || !$rawHeaders) {
            throw new WpApiException(array(
                'error' => ['message' => 'Response is empty'],
                'error_code' => 0
            ));
        }

        $this->responseHeaders = $this->convertHeadersToArray($rawHeaders);
        $this->responseHttpStatusCode = $this->getStatusCodeFromHeader($this->responseHeaders['http_code']);

        return $rawResponse;
    }

    /**
     * @return string
     */
    private function buildHeader()
    {
        $header = [];
        foreach ($this->requestHeaders as $k => $v) {
            $header[] = $k . ': ' . $v;
        }

        return implode("\r\n", $header);
    }

    /**
     * @param array $rawHeaders
     *
     * @return array
     */
    private function convertHeadersToArray(array $rawHeaders)
    {
        $headers = array();

        foreach ($rawHeaders as $line) {
            if (strpos($line, ':') === false) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * @param string $header
     *
     * @return int
     */
    public function getStatusCodeFromHeader($header)
    {
        preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $header, $match);

        return (int)$match[1];
    }

}
