<?php

namespace Lamplight;

use Lamplight\Record\WorkareaSummary;
use Mockery as m;
use Psr\Http\Message\StreamInterface;

class RecordSetTest extends m\Adapter\Phpunit\MockeryTestCase {


    protected $mock_client;

    public function setUp (): void {
        $this->mock_client = m::mock(Client::class);

    }

    protected function prepareClient (string $last_method, string $last_action, $last_response) {

        $this->mock_client->shouldReceive('getLastLamplightMethod')->andReturn($last_method);
        $this->mock_client->shouldReceive('getLastLamplightAction')->andReturn($last_action);
        $this->mock_client->shouldReceive('getLastResponse')->andReturn($last_response);
        $this->mock_client->shouldReceive('getResponseFormat')->andReturn('json');

    }


    protected function prepareResponse (int $status, bool $is_error, string $body_content) {

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

        $record_set = RecordSet::factory($this->mock_client);

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

        $record_set = RecordSet::factory($this->mock_client);

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

        $record_set = RecordSet::factory($this->mock_client);

        $this->assertEquals(2, $record_set->count());
        $this->assertFalse($record_set->getErrors());
        $this->assertEquals(0, $record_set->getErrorCode());
        $this->assertEquals('', $record_set->getErrorMessage());
        $this->assertEquals(200, $record_set->getResponseStatus());
        $this->assertEquals('1, Sports and games Table tennis3, sunbathing4, Learning and Education', $record_set->render());

        $record_set->rewind();
        $record = $record_set->current();
        $this->assertInstanceOf(WorkareaSummary::class, $record);

        $record_set->next();
        $this->assertTrue($record_set->valid());

    }

}
