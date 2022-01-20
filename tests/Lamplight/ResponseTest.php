<?php

namespace Lamplight;

use Lamplight\Response\SuccessResponse;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends m\Adapter\Phpunit\MockeryTestCase {


    /**
     * @var SuccessResponse
     */
    protected $sut;
    /**
     * @var m\LegacyMockInterface|m\MockInterface|ResponseInterface
     */
    protected $guzzle_response;

    public function setUp (): void {
        $this->guzzle_response = m::mock(ResponseInterface::class);
        $this->sut = new SuccessResponse($this->guzzle_response);
    }


    public function test_interface_methods_pass_through () {

        $this->guzzle_response->shouldReceive('getProtocolVersion')->andReturn($protocol_version = '1');

        $this->guzzle_response->shouldReceive('withProtocolVersion')
            ->with($version = 1)
            ->andReturn($with_protocol_version = m::mock(ResponseInterface::class));

        $this->guzzle_response->shouldReceive('getHeaders')->andReturn($headers = []);

        $this->guzzle_response->shouldReceive('hasHeader')
            ->with($head = 'Content-Type')
            ->andReturn($has_headers = false);

        $this->guzzle_response->shouldReceive('getHeader')
            ->with($requested_header = 'Content-size')
            ->andReturn($get_header = []);

        $this->guzzle_response->shouldReceive('getHeaderLine')
            ->with($requested_header_line = 'the header line')
            ->andReturn($get_header_line = 'headerline');

        $this->guzzle_response->shouldReceive('withHeader')
            ->with($wh_name = 'Content-type', $wh_type = 'application/pdf')
            ->andReturn($with_header = m::mock(ResponseInterface::class));

        $this->guzzle_response->shouldReceive('withAddedHeader')
            ->with($wha_name = 'X-Content-type', $wha_type = 'text/html')
            ->andReturn($with_added_header = m::mock(ResponseInterface::class));

        $this->guzzle_response->shouldReceive('withoutHeader')
            ->with($without_header_name = 'Cache')
            ->andReturn($without_header = m::mock(ResponseInterface::class));

        $this->guzzle_response->shouldReceive('getBody')->andReturn($body = m::mock(StreamInterface::class));
        $this->guzzle_response->shouldReceive('withBody')
            ->with($mock_with_body = m::mock(StreamInterface::class))
            ->andReturn($with_body = m::mock(ResponseInterface::class));

        $this->guzzle_response->shouldReceive('getStatusCode')->andReturn($status = 200);
        $this->guzzle_response->shouldReceive('withStatus')
            ->with($with_status_code = 203, $with_reason = 'testing 123')
            ->andReturn($with_status = m::mock(ResponseInterface::class));

        $this->guzzle_response->shouldReceive('getReasonPhrase')->andReturn($reason_phrase = 'reason');


        $this->assertSame($protocol_version, $this->sut->getProtocolVersion());
        $this->assertSame($with_protocol_version, $this->sut->withProtocolVersion($version));
        $this->assertSame($headers, $this->sut->getHeaders());
        $this->assertSame($has_headers, $this->sut->hasHeader($head));
        $this->assertSame($get_header, $this->sut->getHeader($requested_header));
        $this->assertSame($get_header_line,  $this->sut->getHeaderLine($requested_header_line));
        $this->assertSame($with_header, $this->sut->withHeader($wh_name, $wh_type));
        $this->assertSame($with_added_header, $this->sut->withAddedHeader($wha_name, $wha_type));
        $this->assertSame($without_header, $this->sut->withoutHeader($without_header_name));
        $this->assertSame($body, $this->sut->getBody());
        $this->assertSame($with_body, $this->sut->withBody($mock_with_body));
        $this->assertSame($status, $this->sut->getStatusCode());
        $this->assertSame($with_status, $this->sut->withStatus($with_status_code, $with_reason));
        $this->assertSame($reason_phrase, $this->sut->getReasonPhrase());

    }


    public function test_response_error_code () {

        $this->guzzle_response->shouldReceive('getStatusCode')->andReturn(400);
        $this->assertEquals(400, $this->sut->getStatusCode());
        $this->assertTrue($this->sut->isError());
        $this->assertFalse($this->sut->isSuccessful());
    }


    public function test_response_success_code () {

        $this->guzzle_response->shouldReceive('getStatusCode')->andReturn(201);
        $this->assertEquals(201, $this->sut->getStatusCode());
        $this->assertFalse($this->sut->isError());
        $this->assertTrue($this->sut->isSuccessful());
    }

}
