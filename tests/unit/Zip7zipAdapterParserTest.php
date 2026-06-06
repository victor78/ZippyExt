<?php

declare(strict_types=1);

namespace tests\unit;

use Alchemy\Zippy\Parser\ParserInterface;
use Alchemy\Zippy\ProcessBuilder\ProcessBuilderFactoryInterface;
use Alchemy\Zippy\Resource\ResourceManager;
use PHPUnit\Framework\TestCase;
use Victor78\ZippyExt\Adapter\Zip7zipAdapter;

class Zip7zipAdapterParserTest extends TestCase
{
    private function createAdapter(): Zip7zipAdapter
    {
        $parser = $this->createMock(ParserInterface::class);
        $manager = $this->getMockBuilder(ResourceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory = $this->createMock(ProcessBuilderFactoryInterface::class);

        return new Zip7zipAdapter($parser, $manager, $factory, $factory);
    }

    public function testParseFileListingParsesSltOutput(): void
    {
        $adapter = $this->createAdapter();

        $output = <<<'TXT'
Listing archive: archive.zip

--
Path = archive.zip
Type = zip
Physical Size = 123

----------
Path = file1.txt
Folder = -
Size = 6
Modified = 2018-04-04 17:30:44

Path = folder1
Folder = +
Size = 0
Modified =
TXT;

        $members = $adapter->parseFileListing($output);

        $this->assertCount(2, $members);

        $this->assertSame('file1.txt', $members[0]['location']);
        $this->assertSame(6, $members[0]['size']);
        $this->assertFalse($members[0]['is_dir']);

        $this->assertSame('folder1', $members[1]['location']);
        $this->assertSame(0, $members[1]['size']);
        $this->assertTrue($members[1]['is_dir']);
    }

    public function testParseFileListingFallsBackToLegacyFormat(): void
    {
        $adapter = $this->createAdapter();

        $output = <<<'TXT'
header line 1
header line 2
header line 3
2018-04-03 21:56:00 .....            5           33  added.txt
TXT;

        $members = $adapter->parseFileListing($output);

        $this->assertCount(1, $members);
        $this->assertSame('added.txt', $members[0]['location']);
        $this->assertSame(5, $members[0]['size']);
        $this->assertFalse($members[0]['is_dir']);
    }
}
