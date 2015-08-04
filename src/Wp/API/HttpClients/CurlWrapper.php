<?php

namespace Wp\API\HttpClients;

/**
 * Class CurlWrapper
 *
 * @package Wp\API\HttpClients
 */
class CurlWrapper
{
    /**
     * @var resource Curl
     */
    protected $curl = null;


    /**
     * Init
     */
    public function init()
    {
        if ($this->curl === null) {
            $this->curl = curl_init();
        }
    }


    /**
     * Close the resource connection to curl
     */
    public function close()
    {
        curl_close($this->curl);
        $this->curl = null;
    }


    /**
     * Set a curl option
     *
     * @param string $key
     * @param string $value
     */
    public function setOpt($key, $value)
    {
        curl_setopt($this->curl, $key, $value);
    }


    /**
     * Set an array of options
     *
     * @param array $options
     */
    public function setOptArray(array $options)
    {
        curl_setopt_array($this->curl, $options);
    }


    /**
     * Send a curl request
     *
     * @return mixed
     */
    public function exec()
    {
        return curl_exec($this->curl);
    }


    /**
     * Return the curl error number
     *
     * @return int
     */
    public function errNo()
    {
        return curl_errno($this->curl);
    }


    /**
     * Return the curl error message
     *
     * @return string
     */
    public function error()
    {
        return curl_error($this->curl);
    }


    /**
     * Get info from a curl reference
     *
     * @param mixed $type
     *
     * @return mixed
     */
    public function getInfo($type)
    {
        return curl_getinfo($this->curl, $type);
    }


    /**
     * Get the currently installed curl version
     *
     * @return array
     */
    public function version()
    {
        return curl_version();
    }
}