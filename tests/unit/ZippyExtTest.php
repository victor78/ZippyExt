<?php

namespace tests\unit;

require_once 'FileHelper.php';

use Victor78\ZippyExt\Zippy;
use PHPUnit\Framework\TestCase;
class UserTest extends TestCase
{
    const PASSWORD = '12321';
    public $fileHelper;
    
    public function __construct() {
        parent::__construct();
        $this->fileHelper = FileHelper::create();
    }
    public function setUp()
    {
        $this->fileHelper->fillArena();
    }
    
    public function tearDown()
    {
        $this->fileHelper->clearArena();
        $this->fileHelper->clearArchiveArena();
    }
    
    public function testCreateArchRootFiles()
    {
        $zippy = Zippy::load();
        
        $rootFiles = $this->fileHelper->getLevelItems();
        $archiveFile = $this->fileHelper->archiveDir.'/archive.zip';
        
        $archiveZip = $zippy->create($archiveFile, $rootFiles, true, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
    }
    
    public function testCreateArchLevel1Files()
    {
        $zippy = Zippy::load();
        
        $files = $this->fileHelper->getLevelItems(1);
        $archiveFile = $this->fileHelper->archiveDir.'/archive1.zip';
        
        $archiveZip = $zippy->create($archiveFile, $files, true, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
    }
    
    public function testCreateArchLevel01Files()
    {
        $zippy = Zippy::load();
        
        $files0 = $this->fileHelper->getLevelItems(0);
        $files1 = $this->fileHelper->getLevelItems(1);
        $files = array_merge($files0, $files1);
        $configFiles = [];
        foreach ($files as $file){
            $configFiles[str_replace($this->fileHelper->arenaDir.'/', '', $file)] = $file;
        }
        
        $archiveFile = $this->fileHelper->archiveDir.'/archive01.zip';
        
        $archiveZip = $zippy->create($archiveFile, $configFiles, true, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
    }

    
    public function testCreateArchLevel2Files()
    {
        $zippy = Zippy::load();
        
        $files = $this->fileHelper->getLevelItems(2);
        $archiveFile = $this->fileHelper->archiveDir.'/archive2.zip';
        
        $archiveZip = $zippy->create($archiveFile, $files, true, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
    }
    
    public function testCreateArchAllFiles()
    {
        $zippy = Zippy::load();
        
        $files = $this->fileHelper->getLevelItems(0, 1, 1);
        $archiveFile = $this->fileHelper->archiveDir.'/archive_all.zip';
        
        $archiveZip = $zippy->create($archiveFile, $files, true, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
    }
    
    public function testCreateArchAllFilesWithPassword()
    {
        $zippy = Zippy::load();
        
        $files = $this->fileHelper->getLevelItems(0, 1, 1);
        $archiveFile = $this->fileHelper->archiveDir.'/archive_all_pwd.zip';
        
        $archiveZip = $zippy->create($archiveFile, $files, true, '7za', self::PASSWORD);
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
    }
    
    public function testAddMembers()
    {
        $zippy = Zippy::load();
        
        $rootFiles = $this->fileHelper->getLevelItems();
        $archiveFile = $this->fileHelper->archiveDir.'/archive_add.zip';
        
        $archiveZip = $zippy->create($archiveFile, $rootFiles, true, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
        
        $addedFile = $this->fileHelper->getAddedFile();
        $archiveZip->addMembers([$addedFile]);
    }
    
    public function testAddMembersWithPassword()
    {
        $zippy = Zippy::load();
        
        $rootFiles = $this->fileHelper->getLevelItems();
        $archiveFile = $this->fileHelper->archiveDir.'/archive_add_pwd.zip';
        
        $archiveZip = $zippy->create($archiveFile, $rootFiles, true, '7za', self::PASSWORD);
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        
        $isArchiveExists = file_exists($archiveFile);
        $this->assertTrue($isArchiveExists);
        
        $addedFile = $this->fileHelper->getAddedFile();
        $archiveZip->addMembers([$addedFile]);
    }
    
    public function testRemoveMembersWithPassword()
    {
        $zippy = Zippy::load();
        $archiveSource = $this->fileHelper->assetsDir.'/add/archive_add_pwd.zip';
        $archiveDest = $this->fileHelper->archiveDir.'/archive_add_pwd_removing.zip';
        copy($archiveSource, $archiveDest);
        $archiveZip = $zippy->open($archiveDest, '7za', self::PASSWORD);
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        $members = $archiveZip->getMembers();
        $countMembersBegin = count($members);
        $this->assertGreaterThan(0, $countMembersBegin, 'Количество членов должно быть больше 0');
        $member = $members[0];
        $removedFiles = $archiveZip->removeMembers($member->getLocation());
        $newMembers = $archiveZip->getMembers();
        $countMembersEnd = count($newMembers);

        $this->assertNotSame($countMembersBegin, $countMembersEnd);
        
    }
    
    public function testRemoveMembersWithoutPassword()
    {
        $zippy = Zippy::load();
        $archiveSource = $this->fileHelper->assetsDir.'/add/archive_add.zip';
        $archiveDest = $this->fileHelper->archiveDir.'/archive_add_removing.zip';
        copy($archiveSource, $archiveDest);
        $archiveZip = $zippy->open($archiveDest, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        $members = $archiveZip->getMembers();
        $countMembersBegin = count($members);
        $this->assertGreaterThan(0, $countMembersBegin, 'Количество членов должно быть больше 0');
        $member = $members[0];
        $removedFiles = $archiveZip->removeMembers($member->getLocation());
        $newMembers = $archiveZip->getMembers();
        $countMembersEnd = count($newMembers);

        $this->assertNotSame($countMembersBegin, $countMembersEnd);
        
    }
    
    public function testExtract()
    {
        $zippy = Zippy::load();
        $archiveSource = $this->fileHelper->assetsDir.'/add/archive_all.zip';
        $archiveDest = $this->fileHelper->archiveDir.'/archive_extracting.zip';
        copy($archiveSource, $archiveDest);
        $archiveZip = $zippy->open($archiveDest, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        $extractingFolder = $this->fileHelper->makeArenaFolder('extracting');
        $archiveZip->extract($extractingFolder);
        $items = scandir($extractingFolder);
        $items = array_filter($items, function($item){
            return ($item != '.' && $item != '..');
        });
        
        $ok1 = in_array('file1.txt', $items) 
            && in_array('file2.txt', $items) 
            && in_array('file3.txt', $items);
        $ok2 = in_array('folder1', $items)
            && in_array('folder2', $items)
            && in_array('folder_empty', $items);
        
        $this->assertTrue($ok1);
        $this->assertTrue($ok2);
    }
    public function testExtractMembers()
    {
        $zippy = Zippy::load();
        $archiveSource = $this->fileHelper->assetsDir.'/add/archive_all.zip';
        $archiveDest = $this->fileHelper->archiveDir.'/archive_extracting.zip';
        copy($archiveSource, $archiveDest);
        $archiveZip = $zippy->open($archiveDest, '7za');
        $archiveClass = get_class($archiveZip);
        
        $this->assertSame($archiveClass, \Alchemy\Zippy\Archive\Archive::class);
        $extractingFolder = $this->fileHelper->makeArenaFolder('extracting');
        $archiveZip->extract($extractingFolder);
        $items = scandir($extractingFolder);
        $items = array_filter($items, function($item){
            return ($item != '.' && $item != '..');
        });
        
        $ok1 = in_array('file1.txt', $items) 
            && in_array('file2.txt', $items) 
            && in_array('file3.txt', $items);
        $ok2 = in_array('folder1', $items)
            && in_array('folder2', $items)
            && in_array('folder_empty', $items);
        
        $this->assertTrue($ok1);
        $this->assertTrue($ok2);
    }
    
    public function testGetVersion()
    {
        $zippy = Zippy::load();
        $adapter = $zippy->getAdapterFor('7za');
        $version = $adapter->getDeflatorVersion();
        $this->assertTrue(is_numeric($version));
        $this->assertGreaterThan(0, $version);
    }

}