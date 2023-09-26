<?php

namespace Lamplight\Response;

use GuzzleHttp\Psr7\MessageTrait;
use Lamplight\Response;


class ErrorResponse implements Response {

    use MessageTrait;


    /** @var string */
    protected string $reasonPhrase;

    /** @var int */
    protected int $statusCode;

    public function __construct (int $http_code, string $message) {
        $this->statusCode = $http_code;
        $this->reasonPhrase = $message;
    }

    public function getStatus (): int {
        return $this->statusCode;
    }

    public function isError (): bool {
        return true;
    }

    public function isSuccessful (): bool {
        return false;
    }

    public function getStatusCode () : int {
        return $this->statusCode;
    }

    public function withStatus ($code, $reasonPhrase = '') : Response {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = (string) $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase () : string {
        return $this->reasonPhrase;
    }
}