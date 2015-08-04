<?php

namespace Wp\API;

use Wp\API\Exception\WpApiException;
use Wp\API\HttpClients\CurlHttpClient;


/**
 * Class Client
 */
class Client
{
    /**
     * @var string
     */
    private $url = 'https://public-api.wordpress.com';

    /**
     * @var CurlHttpClient
     */
    private $client;

    /**
     * @param string $url
     */
    public function __construct($url = '')
    {
        $this->client = new CurlHttpClient();
        if ($url !== '') {
            $this->url = $url;
        }
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->client->addRequestHeader($key, $value);
        }
    }


    /**
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     */
    public function post($path, array $params = array())
    {
        $url = $this->prepareUrl($path);

        return $this->makeRequest($url, 'POST', $params);
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     */
    public function get($path, array $params = array())
    {
        $url = $this->prepareUrl($path);
        $url .= '?' . http_build_query($params, null, '&');

        return $this->makeRequest($url, 'GET', array());
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function prepareUrl($path)
    {
        $url = $this->url . $path;

        return $url;
    }

    /**
     * @param string $url
     * @param string $methods
     * @param array  $params
     *
     * @return string
     * @throws WpApiException
     */
    private function makeRequest($url, $methods, array $params = array())
    {
        $responseString = $this->client->send($url, $methods, $params);
        $response = json_decode($responseString, true);

        if (is_array($response) && array_key_exists('error', $response)) {
            throw new WpApiException(array(
                'error_code' => $this->client->getResponseHttpStatusCode(),
                'error' => array(
                    'message' => $response['error'],
                ),
            ));
        }

        return $response;
    }
} 