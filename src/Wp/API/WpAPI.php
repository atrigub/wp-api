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
     * Init client
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
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