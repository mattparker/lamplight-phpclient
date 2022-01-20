<?php

namespace Lamplight\Datain;

use Lamplight\Client;
use Lamplight\Datain\Exception\LastRequestWasNotDataInException;
use Mockery as m;
use Psr\Http\Message\StreamInterface;

class ResponseCollectionFactoryTest extends m\Adapter\Phpunit\MockeryTestCase {


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
        $body->shouldReceive('rewind');
        $response->shouldReceive('getBody')->andReturn($body);

        $body->shouldReceive('getContents')->andReturn($json_encoded_response);
        $response->shouldReceive('getStatus')->andReturn($status);

        $this->client->shouldReceive('getLastResponse')->andReturn($response);

        return $response;

    }



    public function test_set_client_which_hasnt_done_datain_throws_exception () {

        $this->prepareLastActionMethod('work', 'one');
        $this->expectException(LastRequestWasNotDataInException::class);
        (new ResponseCollectionFactory())->createResponseFromClient($this->client);

    }



    public function test_single_response_ok_with_id () {

        $content = json_encode(['data' => $id = 123]);
        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id);


        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);


        $this->assertFalse($collection->isMultiple());
        $this->assertTrue($collection->success());
        $this->assertEquals(1, $collection->count(), 'No "child" responses');
        $this->assertEquals(0, $collection->getErrorCode());

        $saved_record_response = $collection->current();
        $this->assertEquals($id, $saved_record_response->getId());
        $this->assertEquals(0, $saved_record_response->getErrorCode());
        $this->assertEquals('', $saved_record_response->getErrorMessage());


    }


    public function test_create_new_record () {

        $content = json_encode(['data' => $id = 123]);
        $this->prepareLastActionMethod('referral', 'add');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn('');

        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);

        $this->assertFalse($collection->isMultiple());
        $this->assertTrue($collection->success());
        $this->assertEquals(1, $collection->count(), 'No "child" responses');
        $this->assertEquals(0, $collection->getErrorCode());

        $single_record = $collection->current();
        $this->assertEquals($id, $single_record->getId());


    }



    public function test_single_json_response_ok_with_id () {

        $content = '{"data":{"id":30936,"attend":true},"meta":""}';
        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);


        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);

        $this->assertFalse($collection->isMultiple());
        $this->assertTrue($collection->success());

        $single_record = $collection->current();
        $this->assertEquals($id, $single_record->getId());

        $this->assertEquals(1, $collection->count(), 'No "child" responses');
        $this->assertEquals(0, $collection->getErrorCode());


    }



    public function test_multiple_response_ok_with_ids () {

        $content = '{"data":[{"id":30936,"attend":true},{"id":30937,"attend":true}],"meta":""}';

        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);


        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);

        $this->assertTrue($collection->isMultiple());
        $this->assertTrue($collection->success());
        $this->assertEquals(2, $collection->count(), 'No "child" responses');
        $this->assertEquals(0, $collection->getErrorCode());

        $first = $collection->current();
        $this->assertEquals($id, $first->getId());
        $this->assertTrue($first->success());


        $collection->next();
        $second = $collection->current();
        $this->assertEquals(30937, $second->getId());
        $this->assertTrue($second->success());
    }


    public function test_multiple_response_with_mixed_responses () {

        $content = '{"data":[{"id":30936,"attend":true},{"id":30937,"attend":false,"error":1026,"msg":"This attendee is already attending the record requested"}],"meta":""}';

        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);


        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);

        $this->assertTrue($collection->isMultiple());
        $this->assertFalse($collection->success());
        $this->assertEquals(2, $collection->count());
        $this->assertEquals(1026, $collection->getErrorCode());

        $first = $collection->current();
        $this->assertEquals($id, $first->getId());
        $this->assertTrue($first->success());


        $collection->next();
        $second = $collection->current();
        $this->assertEquals(30937, $second->getId());
        $this->assertFalse($second->success());
        $this->assertEquals(1026, $second->getErrorCode());
        $this->assertEquals('This attendee is already attending the record requested', $second->getErrorMessage());


    }

    public function test_single_failed_attendance () {
        $content = '{"msg":"This attendee is already attending the record requested","error":1026}';

        $this->prepareLastActionMethod('work', 'attend');
        $this->prepareLastResponse($content, 400);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);

        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);


        $this->assertFalse($collection->success());
        $this->assertEquals(1, $collection->count());
        $this->assertEquals(1026, $collection->getErrorCode());

    }


    public function test_create_relationship () {

        $content = '{"msg":"Relationship created"}';
        $this->prepareLastActionMethod('people', 'relationship');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);

        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);


        $this->assertTrue($collection->success());
        $this->assertEquals(1, $collection->count());
        $this->assertEquals(0, $collection->getErrorCode());
        $this->assertEquals($id, $collection->current()->getId());
    }


    public function test_failed_to_create_relationship () {

        $content = '';
        $this->prepareLastActionMethod('people', 'relationship');
        $this->prepareLastResponse($content, 200);

        $this->client->shouldReceive('getParameter')->with('id')->andReturn($id = 30936);

        $collection = (new ResponseCollectionFactory())->createResponseFromClient($this->client);


        $this->assertFalse($collection->success());
        $this->assertEquals(1, $collection->count());
        $this->assertEquals(1072, $collection->getErrorCode());
        $this->assertEquals($id, $collection->current()->getId());
    }

}
