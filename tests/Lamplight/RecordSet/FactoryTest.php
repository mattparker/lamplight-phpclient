<?php

namespace Lamplight\RecordSet;

use Mockery as m;
use Lamplight\Client;
use Lamplight\Record\WorkareaSummary;
use Lamplight\Response;
use Psr\Http\Message\StreamInterface;

class FactoryTest extends m\Adapter\Phpunit\MockeryTestCase {


    protected $mock_client;
    protected $sut;

    public function setUp (): void {
        $this->mock_client = m::mock(Client::class);
        $this->sut = new Factory();
    }

    protected function prepareClient (string $last_method, string $last_action, $last_response) {

        $this->mock_client->shouldReceive('getLastLamplightMethod')->andReturn($last_method);
        $this->mock_client->shouldReceive('getLastLamplightAction')->andReturn($last_action);
        $this->mock_client->shouldReceive('getLastResponse')->andReturn($last_response);
        $this->mock_client->shouldReceive('getResponseFormat')->andReturn('json');

    }


    protected function prepareResponse (int $status, bool $is_error, ?string $body_content) {

        $response = m::mock(Response::class);
        $response->shouldReceive('getStatus')->andReturn($status);
        $response->shouldReceive('getBody')
            ->andReturn($mock_body = m::mock(StreamInterface::class));
        $mock_body->shouldReceive('getContents')->andReturn($body_content);
        $response->shouldReceive('isError')->andReturn($is_error);

        $response->makePartial();
        return $response;
    }


    public function test_successful_empty_response () {

        $response = $this->prepareResponse(200, false, json_encode([]));
        $this->prepareClient('all', 'workarea', $response);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertEquals(0, $record_set->count());
        $this->assertFalse($record_set->getErrors());
        $this->assertEquals(0, $record_set->getErrorCode());
        $this->assertEquals('', $record_set->getErrorMessage());
        $this->assertEquals(200, $record_set->getResponseStatus());
        $this->assertEquals('', $record_set->render());

    }

    public function test_successful_response_with_one_record_no_template () {

        $data = [
            'data' => [
                ["id" =>"1", "text" => "Sports and games"]
            ]
        ];
        $response = $this->prepareResponse(200, false, json_encode($data));
        $this->prepareClient('all', 'workarea', $response);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertEquals(1, $record_set->count());
        $this->assertFalse($record_set->getErrors());
        $this->assertEquals(0, $record_set->getErrorCode());
        $this->assertEquals('', $record_set->getErrorMessage());
        $this->assertEquals(200, $record_set->getResponseStatus());
        $this->assertEquals('1, Sports and games', $record_set->render());

        $record_set->rewind();
        $record = $record_set->current();
        $this->assertInstanceOf(WorkareaSummary::class, $record);

        $record_set->next();
        $this->assertFalse($record_set->valid());

    }

    public function test_successful_response_with_two_records_no_template () {

        $data = '{"data":[{"id":"1","text":"Sports and games","children":[{"id":"2","text":"Table tennis"},{"id":"3","text":"sunbathing"}]},{"id":"4","text":"Learning and Education","children":[]}],"meta":{"numResults":2,"totalRecords":2}}';
        $response = $this->prepareResponse(200, false, $data);
        $this->prepareClient('all', 'workarea', $response);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertEquals(2, $record_set->count());
        $this->assertFalse($record_set->getErrors());
        $this->assertEquals(0, $record_set->getErrorCode());
        $this->assertEquals('', $record_set->getErrorMessage());
        $this->assertEquals(200, $record_set->getResponseStatus());
        $this->assertEquals(
            '1, Sports and games, 2, Table tennis, 3, sunbathing, 4, Learning and Education, ',
            $record_set->render('', ', ')
        );

        $record_set->rewind();
        $record = $record_set->current();
        $this->assertInstanceOf(WorkareaSummary::class, $record);

        $record_set->next();
        $this->assertTrue($record_set->valid());

    }

    public function test_error_with_response_and_bad_response () {

        $data = ' not json';
        $response = $this->prepareResponse(400, true, $data);
        $this->prepareClient('all', 'workarea', $response);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertTrue($record_set->getErrors());
        $this->assertEquals(1100, $record_set->getErrorCode());

    }


    public function test_error_with_response_and_error_message () {

        $data = json_encode(['error' => 1234, 'msg' => $error_message = 'bad luck']);
        $response = $this->prepareResponse(400, true, $data);
        $this->prepareClient('all', 'workarea', $response);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertTrue($record_set->getErrors());
        $this->assertEquals(1234, $record_set->getErrorCode());
        $this->assertEquals($error_message, $record_set->getErrorMessage());

    }

    public function test_error_with_response_and_empty_response () {

        $data = null;
        $response = $this->prepareResponse(400, true, $data);
        $this->prepareClient('all', 'workarea', $response);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertTrue($record_set->getErrors());
        $this->assertEquals(1100, $record_set->getErrorCode());

    }


    public function test_error_when_server_says_ok_but_content_bad () {

        $data = ' not json';
        $response = $this->prepareResponse(200, false, $data);
        $this->prepareClient('all', 'workarea', $response);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertTrue($record_set->getErrors());
        $this->assertEquals(1100, $record_set->getErrorCode());

    }


    public function test_error_when_no_request_made () {

        $this->prepareClient('all', 'workarea', null);

        $this->expectException(\Exception::class);
        $this->sut->makeRecordSetFromData($this->mock_client);

    }

}
