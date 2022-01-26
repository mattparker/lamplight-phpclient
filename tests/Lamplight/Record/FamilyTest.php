<?php

namespace Lamplight\Record;

use Lamplight\Client;
use Mockery as m;

class FamilyTest extends m\Adapter\Phpunit\MockeryTestCase {



    public function test_method_without_ID_is_add () {

        $client = m::mock(Client::class);

        $sut = new Family();
        $sut->beforeSave($client);

        $this->assertEquals('add', $sut->getLamplightMethod());
        $this->assertEquals('family', $sut->getLamplightAction());

    }


    public function test_method_with_ID_is_update () {

        $client = m::mock(Client::class);

        $sut = new Family(['id' => 123]);
        $sut->beforeSave($client);

        $this->assertEquals('update', $sut->getLamplightMethod());
        $this->assertEquals('family', $sut->getLamplightAction());

    }

}
