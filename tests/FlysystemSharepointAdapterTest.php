<?php
namespace UnitTest\GWSN\FlysystemSharepoint;

use GWSN\FlysystemSharepoint\FlysystemSharepointAdapter;
use GWSN\FlysystemSharepoint\SharepointConnector;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\FilesystemAdapter;



class FlysystemSharepointAdapterTest extends FilesystemAdapterTestCase
{

    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $adapter = new FlysystemSharepointAdapter(
            new SharepointConnector('test', 'test', 'test', 'test'),
    dirname(__DIR__).'/tmp'
        );

        return $adapter;
    }
}
