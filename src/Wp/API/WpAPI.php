<?php

namespace Wp\API;


/**
 * Class WpAPI
 */
class WpAPI
{
    /**
     * @var string
     */
    private $version = 'v1';

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
            'authorization' => 'bearer ' . $accessToken
        ));
    }

    /**
     * @param string $version
     */
    public function setApiVersion($version)
    {
        $this->version = $version;
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
        $path = ltrim($partPath, '/');

        return sprintf('/rest/%s/%s', $this->version, $path);
    }
}