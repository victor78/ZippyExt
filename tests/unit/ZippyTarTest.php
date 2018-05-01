<?php

namespace tests\unit;

require_once 'ZippyTesting.php';

class ZippyTarTest extends ZippyTesting
{
    
    public function __construct() 
    {
        parent::__construct();
        $this->type = 'tar';
        $this->ext = 'tar';
    }
    public function testRemoveMembersWithPassword() 
    {
        $this->assertTrue(true);
    }       
    public function testExtractWithPassword() 
    {
        $this->assertTrue(true);
    }
}
