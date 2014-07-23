<?php

namespace Wp\API;


/**
 * Class WpAPI
 */
class WpAPI
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $accessToken
     */
    public function __construct($accessToken)
    {
        $this->client = new Client();
        $this->client->setHeaders(array(
            'Authorization' => 'Bearer ' . $accessToken
        ));
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     */
    public function get($path, array $params = array())
    {
        return $this->client->get($this->preparePath($path), $params);
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     */
    public function post($path, array $params = array())
    {
        return $this->client->post($this->preparePath($path), $params);
    }

    /**
     * @param string $partPath
     *
     * @return mixed
     */
    private function preparePath($partPath)
    {
        return str_replace('//', '/', '/rest/v1/' . $partPath);
    }
}