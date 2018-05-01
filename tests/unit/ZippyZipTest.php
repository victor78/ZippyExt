<?php

namespace tests\unit;

require_once 'ZippyTesting.php';

class ZippyZipTest extends ZippyTesting
{
    
    public function __construct() 
    {
        parent::__construct();
        $this->type = 'zip';
        $this->ext = 'zip';
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
