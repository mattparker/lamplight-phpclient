<?php

namespace Lamplight\Record;

use Lamplight\Client;
use Mockery as m;

class PeopleTest extends m\Adapter\Phpunit\MockeryTestCase {


    public function test_get_set_params () {

        $sut = new People([
            'first_name' => 'Jo'
        ]);

        $this->assertEquals('Jo', $sut->get('first_name'));
        $this->assertEquals('', $sut->toAPIArray()['role']);
        $this->assertEquals('Jo', $sut->toAPIArray()['first_name']);

        $sut->setRole('user');
        $this->assertEquals('user', $sut->toAPIArray()['role']);
    }

    public function test_set_role_from_client () {

        $client = m::mock(Client::class);
        $client->shouldReceive('getParameter')->once()->with('role')->andReturn('staff');

        $sut = new People();
        $sut->init($client);

        $this->assertEquals('staff', $sut->toAPIArray()['role']);
    }

    public function test_method_without_ID_is_add () {

        $client = m::mock(Client::class);

        $sut = new People();
        $sut->beforeSave($client);

        $this->assertEquals('add', $sut->getLamplightMethod());
        $this->assertEquals('people', $sut->getLamplightAction());

    }


    public function test_method_with_ID_is_add () {

        $client = m::mock(Client::class);

        $sut = new People(['id' => 123]);
        $sut->beforeSave($client);

        $this->assertEquals('update', $sut->getLamplightMethod());
        $this->assertEquals('people', $sut->getLamplightAction());

    }

}
