<?php

namespace Lamplight\Record;

use Lamplight\Record\Exception\MissingAttendeeException;
use Lamplight\Record\Exception\MissingIdException;
use PHPUnit\Framework\TestCase;

class WorkTest extends TestCase {


    public function test_action_method () {

        $sut = new Work();
        $this->assertEquals('attend', $sut->getLamplightMethod());
        $this->assertEquals('work', $sut->getLamplightAction());
    }

    public function test_missing_attendee_is_exception () {
        $sut = new Work();
        $this->expectException(MissingAttendeeException::class);
        $sut->toAPIArray();
    }


    public function test_missing_id_is_exception () {
        $sut = new Work(['attendee' => 'test@hello.com']);
        $this->expectException(MissingIdException::class);
        $sut->toAPIArray();
    }

    public function test_get_values () {
        $sut = new Work(['attendee' => 'test@hello.com', 'id' => 5985]);

        $expected = [
            'id' => 5985, 'attendee' => 'test@hello.com'
        ];
        $this->assertEquals($expected, $sut->toAPIArray());
    }
}
