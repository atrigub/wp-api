<?php

namespace Wp\API;

use Wp\API\Exception\WpApiException;


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
     * @var StreamHttpClient
     */
    private $client;

    /**
     * @param string $url
     */
    public function __construct($url = '')
    {
        $this->client = new StreamHttpClient();
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

        return $this->makeRequest($url, 'GET', $params);
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
    private function makeRequest($url, $methods, array $params)
    {
        $response = $this->client->sendRequest($url, $methods, $params);

        return json_decode($response, true);
    }
} 