<?php

namespace Lamplight;

use Mockery as m;

class ClientTest extends m\Adapter\Phpunit\MockeryTestCase {

    
    public function test_constructor () {

        $guzzle = m::mock(\GuzzleHttp\Client::class);
        $sut = new Client($guzzle, []);
    }


    public function test_constructor_with_config_params () {

        $guzzle = m::mock(\GuzzleHttp\Client::class);
        $sut = new Client($guzzle, ['key' => 'abc123', 'lampid' => 144, 'project' => 2]);
    }
}
