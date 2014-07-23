<?php

namespace Wp\API;


class WpAPI
{
    /**
     * @var Client
     */
    private $client;

    public function __construct($accessToken)
    {
        $this->client = new Client();
        $this->client->setHeaders(array(
            'Authorization' => 'Bearer ' . $accessToken
        ));
    }

    public function get($path, array $params = array())
    {
        return $this->client->get($this->preparePath($path), $params);
    }

    public function post($path, array $params = array())
    {
        return $this->client->post($this->preparePath($path), $params);
    }

    private function preparePath($partPath)
    {
        return str_replace('//', '/', '/rest/v1/' . $partPath);
    }
}