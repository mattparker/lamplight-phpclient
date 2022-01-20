<?php

namespace Lamplight\Datain;


use Lamplight\Response as LamplightResponse;
use Psr\Http\Message\StreamInterface;

/**
 *
 * Lamplight php API client
 *
 * Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * Code licensed under the BSD License:
 * http://www.lamplight-publishing.co.uk/license.php
 *
 * @category   Lamplight
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @version    2.0 New version - Added ResponseCollection to replace Lamplight_Datain_Response
 */
class ResponseCollection implements \Countable, \Iterator, LamplightResponse {

    /**
     * @var SavedRecordResponse[]
     */
    protected array $saved_record_responses = [];
    /**
     * @var int
     */
    protected int $saved_record_pointer = 0;

    /**
     * @var LamplightResponse
     */
    protected LamplightResponse $response;

    /**
     * @var bool
     */
    protected bool $overall_success = true;


    /**
     * @param LamplightResponse $response
     * @param array $saved_record_responses
     */
    public function __construct (LamplightResponse $response, array $saved_record_responses = []) {

        $this->response = $response;
        foreach ($saved_record_responses as $saved_record_response) {
            $this->addSavedResponse($saved_record_response);
        }
    }

    /**
     * @param SavedRecordResponse $saved_record_response
     * @return void
     */
    public function addSavedResponse (SavedRecordResponse $saved_record_response) {
        $this->saved_record_responses[] = $saved_record_response;
        $this->overall_success = $this->overall_success && $saved_record_response->success();
    }

    /**
     * @return bool
     */
    public function isMultiple () : bool {
        return count($this->saved_record_responses) > 1;
    }

    /**
     * @return bool
     */
    public function success () : bool {
        return $this->overall_success;
    }

    /**
     * @return int
     */
    public function getErrorCode () : int {
        foreach ($this->saved_record_responses as $saved_record_response) {
            if ($saved_record_response->getErrorCode() > 0) {
                return $saved_record_response->getErrorCode();
            }
        }
        return 0;
    }

    /**
     * @return string
     */
    public function getErrorMessage () : string {
        foreach ($this->saved_record_responses as $saved_record_response) {
            if ($saved_record_response->getErrorMessage() != '') {
                return $saved_record_response->getErrorMessage();
            }
        }
        return '';
    }


    /**
     * @return int
     */
    public function count () {
        return count($this->saved_record_responses);
    }



    // Iterator methods

    /**
     * @return SavedRecordResponse
     */
    public function current () {
        return $this->saved_record_responses[$this->saved_record_pointer];
    }

    /**
     * @return void
     */
    public function next () {
        ++$this->saved_record_pointer;
    }

    /**
     * @return bool|float|int|mixed|string|null
     */
    public function key () {
        return $this->saved_record_pointer;
    }

    /**
     * @return bool
     */
    public function valid () {
        return isset($this->saved_record_responses[$this->saved_record_pointer]);
    }

    /**
     * @return void
     */
    public function rewind () {
        $this->saved_record_pointer = 0;
    }





    // Response methods

    /**
     * @return string
     */
    public function getProtocolVersion () {
        return $this->response->getProtocolVersion();
    }

    /**
     * @param $version
     * @return LamplightResponse
     */
    public function withProtocolVersion ($version) {
        return $this->response->withProtocolVersion($version);
    }

    /**
     * @return \string[][]
     */
    public function getHeaders () {
        return $this->response->getHeaders();
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader ($name) {
        return $this->response->hasHeader($name);
    }

    /**
     * @param $name
     * @return string[]
     */
    public function getHeader ($name) {
        return $this->response->getHeader($name);
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine ($name) {
        return $this->response->getHeaderLine($name);
    }

    /**
     * @param $name
     * @param $value
     * @return LamplightResponse
     */
    public function withHeader ($name, $value) {
        return $this->response->withHeader($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return LamplightResponse
     */
    public function withAddedHeader ($name, $value) {
        return $this->response->withAddedHeader($name, $value);
    }

    /**
     * @param $name
     * @return LamplightResponse
     */
    public function withoutHeader ($name) {
        return $this->response->withoutHeader($name);
    }

    /**
     * @return StreamInterface
     */
    public function getBody () {
        return $this->response->getBody();
    }

    /**
     * @param StreamInterface $body
     * @return LamplightResponse
     */
    public function withBody (StreamInterface $body) {
        return $this->response->withBody($body);
    }

    /**
     * @return int
     */
    public function getStatus (): int {
        return $this->response->getStatus();
    }

    /**
     * @return bool
     */
    public function isError (): bool {
        return !$this->success();
    }

    /**
     * @return bool
     */
    public function isSuccessful (): bool {
        return $this->success();
    }

    /**
     * @return int
     */
    public function getStatusCode () {
        return $this->response->getStatusCode();
    }

    /**
     * @param $code
     * @param $reasonPhrase
     * @return LamplightResponse
     */
    public function withStatus ($code, $reasonPhrase = '') {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    /**
     * @return string
     */
    public function getReasonPhrase () {
        return $this->response->getReasonPhrase();
    }
}