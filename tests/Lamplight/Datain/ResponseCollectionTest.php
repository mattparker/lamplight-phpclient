<?php

namespace Lamplight\Datain;

use Mockery as m;
use Psr\Http\Message\StreamInterface;

class ResponseCollectionTest extends m\Adapter\Phpunit\MockeryTestCase {

    protected $response;

    public function setUp (): void {

        $this->response = m::mock(\Lamplight\Response::class);

    }


    public function test_empty_error_response () {
        $sut = new ResponseCollection($this->response);

        $this->assertFalse($sut->isMultiple());
        $this->assertEquals(0, $sut->count());
        $this->assertEquals(0, $sut->getErrorCode());
        $this->assertEquals('', $sut->getErrorMessage());
    }

    public function test_interface_methods_pass_through_to_client_response () {

        $response = $this->response;
        $response->shouldReceive('getProtocolVersion')->once();
        $response->shouldReceive('withProtocolVersion')->once();
        $response->shouldReceive('getHeaders')->once();
        $response->shouldReceive('hasHeader')->once();
        $response->shouldReceive('getHeader')->once();
        $response->shouldReceive('getHeaderLine')->once();
        $response->shouldReceive('withHeader')->once();
        $response->shouldReceive('withAddedHeader')->once();
        $response->shouldReceive('withoutHeader')->once();
        $response->shouldReceive('withBody')->once();
        $response->shouldReceive('getStatusCode')->once();
        $response->shouldReceive('withStatus')->once();
        $response->shouldReceive('getReasonPhrase')->once();
        $response->shouldReceive('getBody')->once();
        $response->shouldReceive('getStatus')->once()->andReturn(200);


        $sut = new ResponseCollection($response);

        $sut->getProtocolVersion();
        $sut->withProtocolVersion(1);
        $sut->getHeaders();
        $sut->hasHeader('Content-Type');
        $sut->getHeader('Content-Type');
        $sut->getHeaderLine('Content-Type');
        $sut->withHeader('Content-Type', 'text/html');
        $sut->withAddedHeader('Content-Type', 'text/html');
        $sut->withoutHeader('Content-Type');
        $sut->getBody();
        $sut->withBody(m::mock(StreamInterface::class));
        $sut->getStatus();
        $sut->isError();
        $sut->isSuccessful();
        $sut->getStatusCode();
        $sut->withStatus(201, 'test');
        $sut->getReasonPhrase();


    }

    public function test_iterating_through_when_empty () {

        $sut = new ResponseCollection($this->response);
        $i = 0;
        foreach ($sut as $saved_response) {
            $i++;
        }
        $this->assertEquals(0, $i);

    }



    public function test_with_some_record_responses () {

        $saved_records = [
            $first = new SavedRecordResponse(123, true),
            $second = new SavedRecordResponse(1234, true)
        ];
        $sut = new ResponseCollection($this->response, $saved_records);

        $this->assertTrue($sut->isMultiple());
        $this->assertEquals(0, $sut->getErrorCode());
        $this->assertEquals('', $sut->getErrorMessage());
        $this->assertEquals(2, $sut->count());

        $i = 0;
        foreach ($sut as $saved_response) {
            $i++;
            if ($i == 1) {
                $this->assertSame($first, $saved_response);
            } else {
                $this->assertSame($second, $saved_response);
            }
        }
        $this->assertEquals(2, $i);
    }



    public function test_with_some_failed () {

        $saved_records = [
            $first = new SavedRecordResponse(0, false, 1012, 'testing message 1'),
            $second = new SavedRecordResponse(123, true),
        ];
        $sut = new ResponseCollection($this->response, $saved_records);

        $this->assertTrue($sut->isMultiple());
        $this->assertFalse($sut->isSuccessful());
        $this->assertEquals(1012, $sut->getErrorCode());
        $this->assertEquals('testing message 1', $sut->getErrorMessage());
        $this->assertEquals(2, $sut->count());

        $i = 0;
        foreach ($sut as $saved_response) {
            $i++;
            if ($i == 1) {
                $this->assertSame($first, $saved_response);
            } else {
                $this->assertSame($second, $saved_response);
            }
        }
        $this->assertEquals(2, $i);
    }



}
