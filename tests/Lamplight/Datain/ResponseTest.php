<?php

namespace Lamplight\Datain;

use Lamplight\Client;
use Lamplight\Datain\Exception\LastRequestWasNotDataInException;
use Mockery as m;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends m\Adapter\Phpunit\MockeryTestCase {

    protected $client;

    public function setUp (): void {

        $this->client = m::mock(Client::class);

    }

    protected function prepareLastActionMethod (string $action, string $method) {
        $this->client->shouldReceive('getLastLamplightAction')->andReturn($action);
        $this->client->shouldReceive('getLastLamplightMethod')->andReturn($method);
    }

    protected function prepareLastResponse (string $json_encoded_response, int $status = 200) {

        $response = m::mock(\Lamplight\Response\SuccessResponse::class);
        $body = m::mock(StreamInterface::class);
        $response->shouldReceive('getBody')->andReturn($body);

        $body->shouldReceive('getContents')->andReturn($json_encoded_response);
        $response->shouldReceive('getStatus')->andReturn($status);

        $this->client->shouldReceive('getLastResponse')->andReturn($response);

    }


    public function test_with_no_client () {
        $sut = new Response();
        $this->assertEquals(0, $sut->getId());
    }

    public function test_set_client_which_hasnt_done_datain_throws_exception () {

        $this->prepareLastActionMethod('work', 'one');
        $this->expectException(LastRequestWasNotDataInException::class);
        new Response($this->client);
    }


    public function test_single_response_ok () {

        $content = json_encode(['data' => $id = 123]);
        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id);


        $sut = new Response($this->client);

        $this->assertEquals($id, $sut->getId());
        $this->assertFalse($sut->isMultiple());
        $this->assertTrue($sut->success());
        $this->assertEquals(0, $sut->count(), 'No "child" responses');


    }


    public function test_single_response_ok_with_id () {

        $content = '{"data":{"id":30936,"attend":true},"meta":""}';
        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);


        $sut = new Response($this->client);

        $this->assertEquals($id, $sut->getId());
        $this->assertFalse($sut->isMultiple());
        $this->assertTrue($sut->success());
        $this->assertEquals(0, $sut->count(), 'No "child" responses');


    }

    public function test_multiple_response_ok_with_ids () {

        $content = '{"data":[{"id":30936,"attend":true},{"id":30937,"attend":true}],"meta":""}';

        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);


        $sut = new Response($this->client);


        $this->assertTrue($sut->isMultiple());
        $this->assertTrue($sut->success());
        $this->assertEquals(2, $sut->count(), 'One "child" responses');

        $first = $sut->current();
        $this->assertEquals($id, $first->getId());
        $this->assertTrue($first->success());

        $sut->next();
        $second = $sut->current();
        $this->assertEquals(30937, $second->getId());
        $this->assertTrue($second->success());
    }


    public function test_multiple_response_with_mixed_responses () {

        $content = '{"data":[{"id":30936,"attend":true},{"id":30937,"attend":false,"error":1026,"msg":"This attendee is already attending the record requested"}],"meta":""}';

        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);


        $sut = new Response($this->client);


        $this->assertTrue($sut->isMultiple());
        $this->assertFalse($sut->success());
        $this->assertEquals(2, $sut->count(), 'One "child" responses');

        $first = $sut->current();
        $this->assertEquals($id, $first->getId());
        $this->assertTrue($first->success());

        $sut->next();
        $second = $sut->current();
        $this->assertEquals(30937, $second->getId());
        $this->assertFalse($second->success());
    }


    public function test_single_failed_attendance () {
        $content = '{"msg":"This attendee is already attending the record requested","error":1026}';

        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 400);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);


        $sut = new Response($this->client);

        $this->assertFalse($sut->success());
        $this->assertEquals(1026, $sut->getErrorCode());
    }
}
