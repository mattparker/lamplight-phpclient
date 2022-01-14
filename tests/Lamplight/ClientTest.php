<?php

namespace Lamplight;

use Mockery as m;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends m\Adapter\Phpunit\MockeryTestCase {

    protected $sut;
    protected $mock_guzzle_client;

    public function setUp (): void {

        $this->mock_guzzle_client = m::mock(\GuzzleHttp\Client::class);
        $this->sut = new Client($this->mock_guzzle_client, ['key' => 'abc123', 'lampid' => 144, 'project' => 2]);

    }


    public function test_constructor_with_no_params_gives_exception_on_request () {

        $this->sut = new Client($this->mock_guzzle_client, []);

        $this->expectException(\Exception::class);
        $this->sut->request();
    }


    public function test_constructor_with_config_params_but_no_requested_info_gives_exception () {

        $this->expectException(\Exception::class);
        $this->sut->request();

    }

    public function test_request_workareas_successfully () {

        $response = m::mock(ResponseInterface::class);

        $this->mock_guzzle_client->shouldReceive('request')
            ->with(
                'GET',
                'https://lamplight.online/api/workarea/all/format/json',
                [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2
                ]
            )->andReturn($response);

        $this->sut->fetchWorkarea()->fetchAll()->request();
    }
}
