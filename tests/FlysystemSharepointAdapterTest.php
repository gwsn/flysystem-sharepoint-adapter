<?php
namespace UnitTest\GWSN\FlysystemSharepoint;

use GWSN\FlysystemSharepoint\FlysystemSharepointAdapter;
use GWSN\FlysystemSharepoint\SharepointConnector;
use GWSN\Microsoft\Drive\DriveService;
use GWSN\Microsoft\Drive\FileService;
use GWSN\Microsoft\Drive\FolderService;
use League\Flysystem\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class FlysystemSharepointAdapterTest extends TestCase
{
    /** @var SharepointConnector|MockObject  */
    private $connectorMock;
    /** @var DriveService|MockObject  */
    private $driveMock;
    /** @var FileService|MockObject  */
    private $fileMock;
    /** @var FolderService|MockObject  */
    private $folderMock;
    /** @var FlysystemSharepointAdapter  */
    private FlysystemSharepointAdapter $adapter;
    /** @var string  */
    private string $prefix;

    public function setUp(): void
    {
        $this->driveMock = $this->createMock(DriveService::class);
        $this->fileMock = $this->createMock(FileService::class);
        $this->folderMock = $this->createMock(FolderService::class);
        $this->connectorMock = $this->createMock(SharepointConnector::class);
        $this->prefix = dirname(__DIR__).'/tmp';

        $this->adapter = new FlysystemSharepointAdapter(
            $this->connectorMock,
            $this->prefix
        );
    }

    public function testGetConnector() {

        $connector = $this->adapter->getConnector();

        $this->assertEquals($this->connectorMock, $connector);
    }

    public function testSetConnector() {

        $conn = $this->createMock(SharepointConnector::class);

        $adapter = $this->adapter->setConnector($conn);

        $this->assertEquals($conn, $adapter->getConnector());
    }

    public function testGetPrefix() {
        $prefix = $this->adapter->getPrefix();

        $this->assertEquals($this->prefix, $prefix);
    }


    public function testSetPrefix() {
        $prefix = '/tmp';
        $this->adapter->setPrefix($prefix);

        $this->assertEquals($prefix, $this->adapter->getPrefix());
    }

    public function testSetPrefixWithoutTrailingSlash() {
        $prefix = 'tmp';
        $this->adapter->setPrefix($prefix);

        $this->assertEquals('/'.$prefix, $this->adapter->getPrefix());
    }

    public function testSetPrefixWithTrailingSlash() {
        $prefix = '/tmp/';
        $this->adapter->setPrefix($prefix);

        $this->assertEquals(rtrim($prefix, '/'), $this->adapter->getPrefix());
    }

    public function testFileExists() {
        $path = '/test.txt';
        $prefix = $this->adapter->getPrefix();
        $fullPath = sprintf('%s/%s', $prefix, ltrim($path));

        $this->fileMock->method('checkFileExists')
            ->with($fullPath)
            ->willReturn(true);

        $this->connectorMock->method('getFile')
            ->willReturn($this->fileMock);



        $this->assertTrue($this->adapter->fileExists($path));
    }

    public function testDirectoryExists() {
        $path = '/test';
        $prefix = $this->adapter->getPrefix();
        $fullPath = sprintf('%s/%s', $prefix, ltrim($path));

        $this->folderMock->method('checkFolderExists')
            ->with($fullPath)
            ->willReturn(true);

        $this->connectorMock->method('getFolder')
            ->willReturn($this->folderMock);

        $this->assertTrue($this->adapter->directoryExists($path));
    }


    public function testWrite() {
        $path = '/test';
        $content = 'testContent';

        $options = new Config();
        $prefix = $this->adapter->getPrefix();
        $fullPath = sprintf('%s/%s', $prefix, ltrim($path));

        $this->fileMock->method('writeFile')
            ->with($fullPath, $content, 'text/plain');

        $this->connectorMock->method('getFile')
            ->willReturn($this->fileMock);

        $void = $this->adapter->write($path, $content, $options);

        $this->assertEmpty($void);
    }

    public function testWriteWithOptions() {
        $path = '/test';
        $content = 'testContent';
        $mimeType = ['mimeType' => 'application/json'];
        $options = new Config($mimeType);
        $prefix = $this->adapter->getPrefix();
        $fullPath = sprintf('%s/%s', $prefix, ltrim($path));

        $this->fileMock->method('writeFile')
            ->with($fullPath, $content, $mimeType['mimeType']);

        $this->connectorMock->method('getFile')
            ->willReturn($this->fileMock);

        $void = $this->adapter->write($path, $content, $options);

        $this->assertEmpty($void);
    }


}
