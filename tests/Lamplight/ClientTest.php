<?php

namespace Lamplight;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Lamplight\Client\Exception\MayNotRequestAllWorkException;
use Lamplight\Record\Mutable;
use Lamplight\Response\ErrorResponse;
use Lamplight\Response\SuccessResponse;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends m\Adapter\Phpunit\MockeryTestCase {

    protected $sut;
    protected $mock_guzzle_client;
    protected $mock_response;

    public function setUp (): void {

        $this->mock_guzzle_client = m::mock(\GuzzleHttp\Client::class);
        $this->sut = new Client($this->mock_guzzle_client, ['key' => 'abc123', 'lampid' => 144, 'project' => 2]);

        $this->mock_response = m::mock(ResponseInterface::class);
    }

    protected function expectGuzzleRequest ($method, $url, $params) {
        $this->mock_guzzle_client->shouldReceive('request')
            ->with($method, $url, $params)
            ->andReturn($this->mock_response);
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

        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/workarea/all/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2
                ]
            ]
        );

        $this->sut->fetchWorkarea()->fetchAll()->request();
    }

    public function test_reset_client () {

        $this->expectGuzzleRequest(m::any(), m::any(), m::any());
        $this->sut->fetchWorkarea()->fetchAll()->request();
        $this->sut->resetClient();

        $this->expectException(\Exception::class);
        $this->sut->request();
    }

    public function test_set_api_params () {

        $sut = new Client($this->mock_guzzle_client);
        $sut->setApiParameter('key', 'abc123456');
        $sut->setApiParameter('lampid', '123');
        $sut->setApiParameter('project', '21');

        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/workarea/all/format/json',
            [
                'query' => [
                    'key' => 'abc123456',
                    'lampid' => 123,
                    'project' => 21
                ]
            ]
        );

        $sut->fetchWorkarea()->fetchAll()->request();
    }



    public function test_request_workareas_successfully_with_extra_param () {

        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/workarea/all/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'id' => 99
                ]
            ]
        );

        $this->sut->setParameterGet('id', 99);
        $this->sut->fetchWorkarea()->fetchAll()->request();
    }



    public function test_request_workareas_successfully_with_extra_param_using_POST () {

        $this->expectGuzzleRequest(
            'POST',
            'https://lamplight.online/api/workarea/all/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2
                ],
                'form_params' => [
                    'id' => 99
                ]
            ]
        );

        $this->sut->setMethod('POST');
        $this->sut->setParameterPost('id', 99);
        $this->sut->fetchWorkarea()->fetchAll()->request();
    }


    public function test_fetch_one_work () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/work/one/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'id' => 99
                ]
            ]
        );

        $this->sut->fetchOne(99)->fetchWork()->request();

    }


    public function test_fetch_some_work () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/work/some/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'q' => 'happy'
                ]
            ]
        );

        $this->sut->fetchSome()->fetchWork()->setParameterGet('q', 'happy')->request();

    }

    public function test_fetch_all_work_throws_error () {

        $this->expectException(MayNotRequestAllWorkException::class);

        $this->sut->fetchAll()->fetchWork()->request();

    }


    public function test_fetch_all_orgs () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/orgs/all/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'role' => $role = Client::CONTACT_ROLE
                ]
            ]
        );

        $this->sut->fetchAll()->fetchOrgs($role)->request();

    }


    public function test_fetch_some_orgs () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/orgs/some/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'role' => $role = Client::CONTACT_ROLE
                ]
            ]
        );

        $this->sut->fetchSome()->fetchOrgs($role)->request();

    }


    public function test_fetch_one_org () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/orgs/one/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'id' => $id = 95959,
                    'role' => $role = Client::USER_ROLE
                ]
            ]
        );

        $this->sut->fetchOne($id)->fetchOrgs($role)->request();

    }



    public function test_fetch_all_people () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/people/all/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'role' => $role = Client::CONTACT_ROLE
                ]
            ]
        );

        $this->sut->fetchAll()->fetchPeople($role)->request();

    }


    public function test_fetch_some_people () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/people/some/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'role' => $role = Client::CONTACT_ROLE
                ]
            ]
        );

        $this->sut->fetchSome()->fetchPeople($role)->request();

    }


    public function test_fetch_one_person_short_data () {
        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/people/one/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'id' => $id = 95959,
                    'role' => $role = Client::USER_ROLE,
                    'return' => 'short'
                ]
            ]
        );

        $this->sut->returnShortData();
        $this->sut->fetchOne($id)->fetchPeople($role)->request();

    }


    public function test_fetch_one_person_near_with_full_data () {

        $this->expectGuzzleRequest(
            'GET',
            'https://lamplight.online/api/people/one/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2,
                    'id' => $id = 95959,
                    'role' => $role = Client::USER_ROLE,
                    'near' => $near = 'SW1A 1AA',
                    'nearRadius' => 10000,
                    'return' => 'full'
                ]
            ]
        );

        $this->sut->near($near, 10000)->returnFullData();
        $this->sut->fetchOne($id)->fetchPeople($role)->request();

    }


    public function test_attend_work () {

        $this->expectGuzzleRequest(
            'POST',
            'https://lamplight.online/api/work/attend/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2
                ],
                'form_params' => [
                    'id' => $id = 95959,
                    'attendee' => $attendee_id = 12323
                ]
            ]
        );

        $this->sut->attendWork($id, $attendee_id)->request();

    }


    public function test_save_mutable_record () {

        $record = m::mock(Mutable::class);
        $record->shouldReceive('beforeSave')->once()->with($this->sut);
        $record->shouldReceive('getLamplightMethod')->once()->andReturn('add');
        $record->shouldReceive('getLamplightAction')->once()->andReturn('referral');
        $record->shouldReceive('toAPIArray')->once()
            ->andReturn($data = [
                'id' => 94949,
                'workareaid' => 123,
                'reason' => 'test 49',
                'attendee' => 'testing@lamplightdb.co.uk'
            ]);
        $record->shouldReceive('afterSave')->once()->with($this->sut, m::any());

        $this->expectGuzzleRequest(
            'POST',
            'https://lamplight.online/api/referral/add/format/json',
            [
                'query' => [
                    'key' => 'abc123',
                    'lampid' => 144,
                    'project' => 2
                ],
                'form_params' => $data
            ]
        );

        $this->sut->save($record);

        $this->assertEquals(94949, $this->sut->getParameter('id'));
        $this->assertEquals(144, $this->sut->getParameter('lampid'));
        $this->assertEquals(null, $this->sut->getParameter('not known'));
        $this->assertEquals('add', $this->sut->getLastLamplightMethod());
        $this->assertEquals('referral', $this->sut->getLastLamplightAction());
    }

    public function test_the_response_when_ok () {

        $this->expectGuzzleRequest(m::any(), m::any(), m::any());

        $response = $this->sut->fetchOne(123)->fetchPeople(Client::FUNDER_ROLE)->request();


        $this->assertInstanceOf(SuccessResponse::class, $response);
        $this->assertSame($response, $this->sut->getLastResponse());

    }


    public function test_the_response_with_error () {

        $this->mock_guzzle_client->shouldReceive('request')
            ->andThrow(new \Exception($error_message = 'Not allowed', $error_code = 403));


        $response = $this->sut->fetchOne(123)->fetchPeople(Client::FUNDER_ROLE)->request();


        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->assertSame($response, $this->sut->getLastResponse());
        $this->assertEquals($error_code, $response->getStatus());
        $this->assertEquals($error_message, $response->getReasonPhrase());

    }

}
