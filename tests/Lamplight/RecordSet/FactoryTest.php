<?php

namespace Lamplight\RecordSet;

use Mockery as m;
use Lamplight\Client;
use Lamplight\Record\WorkareaSummary;
use Lamplight\Response\SuccessResponse;
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

        $response = m::mock(SuccessResponse::class);
        $response->shouldReceive('getStatus')->andReturn($status);
        $response->shouldReceive('getBody')
            ->andReturn($mock_body = m::mock(StreamInterface::class));
        $mock_body->shouldReceive('rewind');
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

    public function test_successful_response_single_record_for_profile () {

        $data = '{"data":{"id":"653","name":"Example Staff-Member","summary":"","date_updated":"2022-01-14 16:03:05","first_name":"Example","surname":"Staff-Member","address_line_1":"13a","postcode":"SW1A 1AA","lat":"51.5010089386737000","lng":"-0.141588","northing":"179645","easting":"529090","email":"testing@lamplightdb.co.uk","mobile":"","phone":"","web":"","Food_liked":["Bread","Cheese"]},"meta":{"numRecords":17,"totalRecords":1}}';
        $response = $this->prepareResponse(200, false, $data);
        $this->prepareClient('one', 'people', $response);
        $this->mock_client->shouldReceive('getParameter')->once()->with('role')->andReturn(Client::FUNDER_ROLE);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertEquals(1, $record_set->count());
        $record = $record_set->current();

        $this->assertEquals(653, $record->get('id'));
        $this->assertEquals("Example Staff-Member", $record->get('name'));
    }


    public function test_success_response_single_work () {

        $data = '{"data":{"id":"30936","title":"My published record","workarea":"1","workareaText":"Sports and games","start_date":"2022-01-14 16:15:00","end_date":"2022-01-14 16:15:00","may_add_attend":true,"date_updated":"2022-01-14 17:06:25","subWorkareas":[],"location":[],"location_full_details":[],"summary":"","description":"","followup":"","num_users_attending":"1","maximum_num_users_allowed":"15"},"meta":{"numRecords":1,"totalRecords":1}}';
        $response = $this->prepareResponse(200, false, $data);
        $this->prepareClient('one', 'people', $response);
        $this->mock_client->shouldReceive('getParameter')->once()->with('role')->andReturn(Client::FUNDER_ROLE);

        $record_set = $this->sut->makeRecordSetFromData($this->mock_client);

        $this->assertEquals(1, $record_set->count());
        $record = $record_set->current();
        $this->assertEquals(30936, $record->get('id'));
        $this->assertEquals("My published record", $record->get('title'));

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

        $data = '';
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
