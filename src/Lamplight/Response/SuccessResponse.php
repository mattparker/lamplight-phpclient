<?php

namespace Lamplight\Response;


use Lamplight\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Wraps the Guzzle Response and passes through interface methods.
 * Also implements some of the previous \Zend_Http_Response methods that are likely to be used
 * in implementations
 */
class SuccessResponse implements Response {


    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $guzzle_response;


    /**
     * @param ResponseInterface $guzzle_response
     */
    public function __construct (ResponseInterface $guzzle_response) {

        $this->guzzle_response = $guzzle_response;
    }


    /*
     * Methods implementing previous \Zend_Http_Response methods commonly used
     */
    /**
     * @return int
     */
    public function getStatus () : int {
        return $this->getStatusCode();
    }

    /**
     * @return bool
     */
    public function isError () : bool {
        $first_digit = $this->getFirstDigitOfStatus();
        return ($first_digit == 4 || $first_digit == 5);
    }

    /**
     * @return bool
     */
    public function isSuccessful () : bool {
        $first_digit = $this->getFirstDigitOfStatus();
        return ($first_digit == 2 || $first_digit == 1);
    }

    /**
     * @return float
     */
    protected function getFirstDigitOfStatus () {
        return floor($this->getStatusCode() / 100);
    }






    /*
     * Pass through methods of interface
     */

    /**
     * @return string
     */
    public function getProtocolVersion () {
        return $this->guzzle_response->getProtocolVersion();
    }

    /**
     * @param $version
     * @return SuccessResponse|ResponseInterface
     */
    public function withProtocolVersion ($version) {
        return $this->guzzle_response->withProtocolVersion($version);
    }

    /**
     * @return \string[][]
     */
    public function getHeaders () {
        return $this->guzzle_response->getHeaders();
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader ($name) {
        return $this->guzzle_response->hasHeader($name);
    }

    /**
     * @param $name
     * @return string[]
     */
    public function getHeader ($name) {
        return $this->guzzle_response->getHeader($name);
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine ($name) {
        return $this->guzzle_response->getHeaderLine($name);
    }

    /**
     * @param $name
     * @param $value
     * @return SuccessResponse|ResponseInterface
     */
    public function withHeader ($name, $value) {
        return $this->guzzle_response->withHeader($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return SuccessResponse|ResponseInterface
     */
    public function withAddedHeader ($name, $value) {
        return $this->guzzle_response->withAddedHeader($name, $value);
    }

    /**
     * @param $name
     * @return SuccessResponse|ResponseInterface
     */
    public function withoutHeader ($name) {
        return $this->guzzle_response->withoutHeader($name);
    }


    /**
     * @return StreamInterface
     */
    public function getBody () {
        return $this->guzzle_response->getBody();
    }

    /**
     * @param StreamInterface $body
     * @return SuccessResponse|ResponseInterface
     */
    public function withBody (StreamInterface $body) {
        return $this->guzzle_response->withBody($body);
    }

    /**
     * @return int
     */
    public function getStatusCode () {
        return $this->guzzle_response->getStatusCode();
    }

    /**
     * @param $code
     * @param $reasonPhrase
     * @return SuccessResponse|ResponseInterface
     */
    public function withStatus ($code, $reasonPhrase = '') {
        return $this->guzzle_response->withStatus($code, $reasonPhrase);
    }

    /**
     * @return string
     */
    public function getReasonPhrase () {
        return $this->guzzle_response->getReasonPhrase();
    }
}
