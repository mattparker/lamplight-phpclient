<?php

namespace Lamplight\Record;

use PHPUnit\Framework\TestCase;

class GroupMembershipTest extends TestCase {


    public function test_minimal_set_with_method () {

        $sut = new GroupMembership();
        $sut->setGroupMembership($id = 343, $group = 993);
        $this->assertEquals(
            [
                'id' => $id,
                'group_id' => $group,
                'notes' => '',
                'date_joined' => ''
            ],
            $sut->toAPIArray()
        );
    }

    public function test_set_all_with_method () {

        $sut = new GroupMembership();
        $sut->setGroupMembership($id = 343, $group = 993, $notes = 'testing 123', new \DateTime($date = '2022-02-12 10:14:45'));
        $this->assertEquals(
            [
                'id' => $id,
                'group_id' => $group,
                'notes' => $notes,
                'date_joined' => $date
            ],
            $sut->toAPIArray()
        );
    }


    public function test_set_all_in_constructor () {

        $sut = new GroupMembership([
            'id' => $id = 343,
            'group_id' => $group = 993,
            'notes' => $notes = 'testing 123',
            'date_joined' => new \DateTime($date = '2022-02-12 10:14:45')
        ]);
        $this->assertEquals(
            [
                'id' => $id,
                'group_id' => $group,
                'notes' => $notes,
                'date_joined' => $date
            ],
            $sut->toAPIArray()
        );
    }


    public function test_set_date_as_a_string_in_constructor () {

        $sut = new GroupMembership([
            'id' => $id = 343,
            'group_id' => $group = 993,
            'notes' => $notes = 'testing 123',
            'date_joined' => $date = '2022-02-12 10:14:45'
        ]);
        $this->assertEquals(
            [
                'id' => $id,
                'group_id' => $group,
                'notes' => $notes,
                'date_joined' => $date
            ],
            $sut->toAPIArray()
        );
    }

}
