<?php

namespace Lamplight;

use Psr\Http\Message\ResponseInterface;

interface Response extends ResponseInterface {

    /**
     * @return int
     */
    public function getStatus () : int;

    /**
     * @return bool
     */
    public function isError () : bool;

    /**
     * @return bool
     */
    public function isSuccessful () : bool;

}