<?php

namespace SandboxTests;

use Lamplight\Record\People;
use Lamplight\Record\Referral;
use Lamplight\Record\Work;
use PHPUnit\Framework\TestCase;
use Lamplight\Client;

/**
 * Tests that can run against a live server
 *
 * NB network connection is required
 *
 */
class GetLiveDataFromSandboxTest extends TestCase {

    protected $sut;

    public function setUp (): void {
        $key = '924745a1fc33ce412c55ab0698891b45951c16e8ad738241baed9c4214759f57';
        $lampid = 561;
        $projectid = 4;

        $this->sut = new Client(null, ['key' => $key, 'lampid' => $lampid, 'project' => $projectid]);

    }


    public function test_live_workareas () {

        $workareas = $this->sut->fetchWorkarea()->fetchAll()->request();

        $expected = '{"data":[{"id":"1","text":"Sports and games","children":[{"id":"2","text":"Table tennis"},{"id":"3","text":"sunbathing"}]},{"id":"4","text":"Learning and Education","children":[]}],"meta":{"numResults":2,"totalRecords":2}}';
        $this->assertEquals($expected, $workareas->getBody()->getContents());
    }

    public function test_get_profile () {

        $profile_response = $this->sut->fetchPeople('user')->fetchOne(653)->request();
        $expected = '{"data":{"id":"653","name":"Example Staff-Member","summary":"","date_updated":"2022-01-14 16:03:05","first_name":"Example","surname":"Staff-Member","address_line_1":"13a","postcode":"SW1A 1AA","lat":"51.5010089386737000","lng":"-0.141588","northing":"179645","easting":"529090","email":"testing@lamplightdb.co.uk","mobile":"","phone":"","web":"","Food_liked":["Bread","Cheese"]},"meta":{"numRecords":17,"totalRecords":1}}';
        $stream = $profile_response->getBody();
        $this->assertEquals($expected, $stream->getContents());

        $this->assertTrue($profile_response->isSuccessful());
        $records = $this->sut->getRecordSet();
        $records->rewind();
        $this->assertEquals(1, $records->count());
        $profile = $records->current();
        $this->assertInstanceOf(People::class, $profile);
        $this->assertEquals("Example Staff-Member", $profile->get('name'));
        $this->assertEquals(["Bread", "Cheese"], $profile->get('Food_liked'));

    }

    public function test_get_all_profiles () {

        $profiles = $this->sut->fetchPeople('user')->fetchSome()->request();
        $expected = '{"data":[{"id":"653","name":"Example Staff-Member","summary":""}],"meta":{"numRecords":1,"totalRecords":"1"}}';
        $this->assertEquals($expected, $profiles->getBody()->getContents());
    }

    public function test_get_profiles_near () {

        $profiles = $this->sut->fetchPeople('user')->fetchSome()->near('WC2E 7LJ', 10000)->request();
        $expected = '{"data":[{"id":"653","name":"Example Staff-Member","summary":"","date_updated":"2022-01-14 16:03:05","first_name":"Example","surname":"Staff-Member","address_line_1":"13a","postcode":"SW1A 1AA","lat":"51.5010089386737000","lng":"-0.141588","northing":"179645","easting":"529090","email":"testing@lamplightdb.co.uk","mobile":"","phone":"","web":"","Food_liked":["Bread","Cheese"]}],"meta":{"numRecords":1,"totalRecords":"1","geosearchCentre":{"lat":51.51104265259142,"lng":-0.12280484080767042}}}';
        $this->assertEquals($expected, $profiles->getBody()->getContents());
    }

    public function test_get_work () {
        $work = $this->sut->fetchWork()->fetchOne(30936)->request();
        $expected = '{"data":{"id":"30936","title":"My published record","workarea":"1","workareaText":"Sports and games","start_date":"2022-01-14 16:15:00","end_date":"2022-01-14 16:15:00","may_add_attend":true,"date_updated":"2022-01-14 16:06:32","subWorkareas":[],"location":[],"location_full_details":[],"summary":"","description":"","followup":"","num_users_attending":"0","maximum_num_users_allowed":"15"},"meta":{"numRecords":1,"totalRecords":1}}';
        $this->assertEquals($expected, $work->getBody()->getContents());
    }

    public function test_add_attendee_to_work () {
        $record = new Work(['id' => 30936, 'attendee' => 653]);
        $response = $this->sut->save($record);

        $expected = '{"data":{"id":30936,"attend":true},"meta":""}';
        $this->assertEquals($expected, $response->getBody()->getContents());
    }

    public function test_add_attendance_to_many_work_records () {

        $response = $this->sut->attendWork('30936,30937', 653)->request();
        $expected = '{"data":[{"id":30936,"attend":true},{"id":30937,"attend":true}],"meta":""}';
        $this->assertEquals($expected, $response->getBody()->getContents());

        $datain_response = $this->sut->getDatainResponse();

      //  $this->assertTrue($datain_response->isMultiple());

        $datain_response->rewind();
        $first = $datain_response->current();
        $this->assertEquals(30936, $first->getId());
        $this->assertTrue($first->success());

        $datain_response->next();
        $second = $datain_response->current();
        $this->assertEquals(30937, $second->getId());
        $this->assertTrue($second->success());

    }

    public function test_add_referral () {
        $record = new Referral(['attendee' => 653, 'reason' => 'testing 44', 'date' => '2022-01-01 13:13']);
        $response = $this->sut->save($record);

        $expected = '{"data":{"id":30939,"attend":true},"meta":""}';
        $this->assertEquals($expected, $response->getBody()->getContents());
    }
}