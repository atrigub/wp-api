<?php

namespace Wp\API;


use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Request;

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
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var array
     */
    private $headers = array();

    /**
     * @param string $url
     */
    public function __construct($url = '')
    {
        $this->client = new GuzzleClient();
        if ($url !== '') {
            $this->url = $url;
        }
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function post($path, array $params = array())
    {
        $url = $this->prepareUrl($path);

        $request = $this->client->createRequest('POST', $url, array(
            'body' => $params
        ));

        return $this->makeRequest($request);
    }

    public function get($path, array $queryParams = array())
    {
        $url = $this->prepareUrl($path);

        $request = $this->client->createRequest('GET', $url);
        $request->setQuery($queryParams);

        return $this->makeRequest($request);
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
     * @param Request $request
     *
     * @return mixed
     * @throws Exception\WpApiException
     */
    private function makeRequest(Request $request)
    {
        $request->addHeaders($this->headers);
        try {
            $response = $this->client->send($request);

            return $response->json();
        } catch (ClientException $e) {
            throw new WpApiException(array(
                'error_code' => intval($e->getResponse()->getStatusCode()),
                'error' => array(
                    'message' => $e->getResponse()->getReasonPhrase(),
                ),
            ));
        }
    }
} 