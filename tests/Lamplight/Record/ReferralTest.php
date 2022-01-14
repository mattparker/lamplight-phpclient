<?php

namespace Lamplight\Record;

use Lamplight\Record\Exception\MissingAttendeeException;
use Mockery as m;

class ReferralTest extends m\Adapter\Phpunit\MockeryTestCase {

    public function test_SetDate () {

        $sut = new Referral();
        $sut->setDate($date = '2022-01-01 14:55:00');
        $sut->setAttendee(123);

        $expected = [
            'date_from' => $date,
            'attendee' => 123,
            'workareaid' => '',
            'referral_reason' => ''
        ];
        $this->assertEquals($expected, $sut->toAPIArray());

    }

    public function test_set_date_from_object () {

        $sut = new Referral();
        $sut->setDate(new \DateTime($date = '2022-01-01 14:55:00'));
        $sut->setAttendee(123);

        $expected = [
            'date_from' => $date,
            'attendee' => 123,
            'workareaid' => '',
            'referral_reason' => ''
        ];
        $this->assertEquals($expected, $sut->toAPIArray());
    }

    public function test_set_date_from_object_in_constructor () {

        $sut = new Referral([
            'date' => new \DateTime($date = '2022-01-01 14:55:00'),
            'attendee' => 123,
            'reason' => $reason = 'tea and scones',
            'workareaid' => 55
        ]);

        $expected = [
            'date_from' => $date,
            'attendee' => 123,
            'workareaid' => 55,
            'referral_reason' => $reason
        ];
        $this->assertEquals($expected, $sut->toAPIArray());
    }

    public function test_set_custom_fields () {
        $sut = new Referral([
            'date' => new \DateTime($date = '2022-01-01 14:55:00'),
            'attendee' => 123
        ]);
        $sut->set('my_field', 'hats');

        $expected = [
            'date_from' => $date,
            'attendee' => 123,
            'workareaid' => '',
            'referral_reason' => '',
            'my_field' => 'hats'
        ];
        $this->assertEquals($expected, $sut->toAPIArray());

    }


    public function test_missing_attendee_is_exceptional () {

        $sut = new Referral();
        $sut->setDate($date = '2022-01-01 14:55:00');

        $this->expectException(MissingAttendeeException::class);
        $sut->toAPIArray();

    }

}
