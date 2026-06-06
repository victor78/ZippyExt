<?php

namespace tests\unit;

require_once 'ZippyTesting.php';

class ZippyTarTest extends ZippyTesting
{
    public $type = 'tar';
    public $ext = 'tar';

    public function testRemoveMembersWithPassword()
    {
        $this->markTestSkipped('GNU tar adapter does not support password protection.');
    }

    public function testExtractWithPassword()
    {
        $this->markTestSkipped('GNU tar adapter does not support password protection.');
    }

    public function testCreateArchAllFilesWithPassword()
    {
        $this->markTestSkipped('GNU tar adapter does not support password protection.');
    }

    public function testAddMembersWithPassword()
    {
        $this->markTestSkipped('GNU tar adapter does not support password protection.');
    }
}
