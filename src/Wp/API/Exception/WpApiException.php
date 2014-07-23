<?php

namespace Wp\API\Exception;


/**
 * Class WpApiException
 */
class WpApiException extends \Exception
{
    /**
     * The result from the API server that represents the exception information.
     *
     * @var mixed
     */
    protected $result;

    /**
     * Make a new API Exception with the given result.
     *
     * @param array $result The result from the API server
     */
    public function __construct($result)
    {
        $this->result = $result;

        $code = 0;
        if (isset($result['error_code']) && is_int($result['error_code'])) {
            $code = $result['error_code'];
        }

        $msg = 'Unknown Error. Check getResult()';

        if (isset($result['error']['message'])) {
            $msg = $result['error']['message'];
        }

        parent::__construct($msg, $code);
    }

    /**
     * Return the associated result object returned by the API server.
     *
     * @return array The result from the API server
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * To make debugging easier.
     *
     * @return string The string representation of the error
     */
    public function __toString()
    {
        $str = 'WpApiException' . ': ';
        if ($this->code != 0) {
            $str .= $this->code . ': ';
        }
        return $str . $this->message;
    }
}