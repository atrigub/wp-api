<?php

namespace Wp\API;


class WpAuth
{
    const AUTHORIZATION_URL = 'https://public-api.wordpress.com/oauth2/authorize';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @var string
     */
    protected $responseType;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param string $responseType
     * @param string $scope
     */
    public function __construct($clientId, $clientSecret, $redirectUrl, $responseType = 'code', $scope = 'global')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->responseType = $responseType;
        $this->scope = $scope;

        $this->client = new Client();
    }

    public function getAuthUrl()
    {
        return self::AUTHORIZATION_URL . '?' . http_build_query(array(
                'client_id' => $this->clientId,
                'redirect_uri' => $this->redirectUrl,
                'response_type' => $this->responseType
            ));
    }

    public function getToken($code)
    {
       return $this->client->post('/oauth2/token', array(
           'client_id' => $this->clientId,
           'redirect_uri' => $this->redirectUrl,
           'client_secret' => $this->clientSecret,
           'code' => $code,
           'grant_type' => 'authorization_code'
       ));
    }
}