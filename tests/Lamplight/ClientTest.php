<?php

namespace Lamplight;

use Mockery as m;

class ClientTest extends m\Adapter\Phpunit\MockeryTestCase {

    
    public function test_one () {

        $guzzle = m::mock(\GuzzleHttp\Client::class);
        $sut = new Client($guzzle, []);
    }
}
