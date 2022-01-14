<?php

namespace Lamplight;

use Lamplight\Record\BaseRecord;
use Mockery as m;

class RecordSetTest extends m\Adapter\Phpunit\MockeryTestCase {


    public function test_record_set_with_no_records () {

        $sut = new RecordSet;
        $this->assertEquals(0, $sut->count());
        $this->assertFalse($sut->getErrors());
        $this->assertEquals(0, $sut->getErrorCode());
        $this->assertEquals(0, $sut->getResponseStatus());
        $this->assertEquals('', $sut->getRecordTemplate());
        $this->assertEquals('', $sut->render());
        $this->assertEquals('s', $sut->plural());
        $this->assertEquals('', $sut->getErrorMessage());

        $iterated = false;
        foreach ($sut as $record) {
            $iterated = true;
        }
        $this->assertFalse($iterated);
    }

    public function test_record_set_with_no_records_set_errors () {

        $sut = new RecordSet;

        $sut->setErrorCode($code = 1123);
        $sut->setErrorMessage($err_message = 'oh dear');
        $sut->setErrors(true);
        $sut->setRecordTemplate($template = 'put {data} here');
        $sut->setResponseStatus(403);

        $this->assertEquals(0, $sut->count());
        $this->assertTrue($sut->getErrors());
        $this->assertEquals($code, $sut->getErrorCode());
        $this->assertEquals($err_message, $sut->getErrorMessage());
        $this->assertEquals(403, $sut->getResponseStatus());
        $this->assertEquals($template, $sut->getRecordTemplate());
        $this->assertEquals('', $sut->render());
        $this->assertEquals('s', $sut->plural());

        $iterated = false;
        foreach ($sut as $record) {
            $iterated = true;
        }
        $this->assertFalse($iterated);
    }


    public function test_with_one_record () {

        $sut = new RecordSet([$record_1 = m::mock(BaseRecord::class)]);
        $record_1->shouldReceive('render')->once()->andReturn($rendered_record = 'THIS IS MY RECORD');

        $this->assertEquals(1, $sut->count());
        $this->assertEquals('', $sut->plural());
        $this->assertEquals($rendered_record, $sut->render());
    }


    public function test_with_one_record_and_template () {

        $sut = new RecordSet([$record_1 = m::mock(BaseRecord::class)]);
        $record_1->shouldReceive('render')
            ->once()
            ->with($template = 'record {Phere}')
            ->andReturn($rendered_record = 'THIS IS MY RECORD');

        $this->assertEquals(1, $sut->count());
        $this->assertEquals('', $sut->plural());
        $this->assertEquals($rendered_record, $sut->render($template));
    }

    public function test_with_two_records () {

        $sut = new RecordSet([
            $record_1 = m::mock(BaseRecord::class),
            $record_2 = m::mock(BaseRecord::class)
        ]);
        $record_1->shouldReceive('render')->once()->andReturn($rendered_record = 'THIS IS MY RECORD');
        $record_2->shouldReceive('render')->once()->andReturn($rendered_record2 = 'pianissimo');

        $this->assertEquals(2, $sut->count());
        $this->assertEquals('s', $sut->plural());
        $this->assertEquals($rendered_record . $rendered_record2, $sut->render());
    }

}
