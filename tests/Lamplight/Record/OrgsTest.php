<?php

namespace Lamplight\Record;

use Lamplight\Client;
use Mockery as m;

class OrgsTest extends m\Adapter\Phpunit\MockeryTestCase {


    public function test_method_without_ID_is_add () {

        $client = m::mock(Client::class);

        $sut = new Orgs();
        $sut->beforeSave($client);

        $this->assertEquals('add', $sut->getLamplightMethod());
        $this->assertEquals('orgs', $sut->getLamplightAction());

    }

}
