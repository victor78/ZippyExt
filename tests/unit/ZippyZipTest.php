<?php

namespace tests\unit;

require_once 'ZippyTesting.php';

class ZippyZipTest extends ZippyTesting
{
    public $type = 'zip';
    public $ext = 'zip';

    public function testRemoveMembersWithPassword()
    {
        $this->markTestSkipped('Standard zip adapter does not support password protection.');
    }

    public function testExtractWithPassword()
    {
        $this->markTestSkipped('Standard zip adapter does not support password protection.');
    }

    public function testCreateArchAllFilesWithPassword()
    {
        $this->markTestSkipped('Standard zip adapter does not support password protection.');
    }

    public function testAddMembersWithPassword()
    {
        $this->markTestSkipped('Standard zip adapter does not support password protection.');
    }
}
