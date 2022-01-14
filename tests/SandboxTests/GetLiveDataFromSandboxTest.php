<?php

namespace SandboxTests;

use PHPUnit\Framework\TestCase;
use Lamplight\Client;

/**
 * Tests that can run against a live server
 *
 * NB network connection is required
 *
 */
class GetLiveDataFromSandboxTest extends TestCase {



    public function test_live_workareas () {
        $key = '1aed9b195f6f6b5c38254f006750d5b4275c0473e118a79cf3d80bea894f7eb2';
        $lampid = 561;
        $projectid = 1;

        $this->sut = new Client(null, ['key' => $key, 'lampid' => $lampid, 'project' => $projectid]);


        $workareas = $this->sut->fetchWorkarea()->fetchAll()->request();

        $expected = '{"data":[{"id":"1","text":"Sports and games","children":[{"id":"2","text":"Table tennis"},{"id":"3","text":"sunbathing"}]},{"id":"4","text":"Learning and Education","children":[]}],"meta":{"numResults":2,"totalRecords":2}}';
        $this->assertEquals($expected, $workareas->getBody()->getContents());
    }
}