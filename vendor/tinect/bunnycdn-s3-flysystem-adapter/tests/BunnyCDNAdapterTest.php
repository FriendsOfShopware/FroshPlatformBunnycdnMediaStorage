<?php

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Tinect\Flysystem\BunnyCDN\BunnyCDNAdapter;

class BunnyCDNAdapterTest extends TestCase
{
    const TEST_FILE_CONTENTS = 'testing1982';

    private $subfolder = 'teeeeeest';

    private function getBunnyCDNAdapter(): BunnyCDNAdapter
    {
        if (!isset($_SERVER['STORAGENAME'], $_SERVER['APIKEY'])) {
            throw new RuntimeException('Running test without real data is currently not possible');
        }

        return new BunnyCDNAdapter($_SERVER['STORAGENAME'], $_SERVER['APIKEY'], 'storage.bunnycdn.com', $this->subfolder);
    }

    public function testFileProcesses()
    {
        $adapter = $this->getBunnyCDNAdapter();

        self::assertFalse(
            $adapter->has('testing/test.txt')
        );

        self::assertIsArray(
            $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config())
        );

        self::assertTrue(
            $adapter->has('testing/test.txt')
        );

        self::assertTrue(
            $adapter->has('/testing/test.txt')
        );

        self::assertEquals(
            self::TEST_FILE_CONTENTS,
            $adapter->read('/testing/test.txt')['contents']
        );

        self::assertTrue(
            $adapter->delete('testing/test.txt')
        );
    }

    /**
     * @throws Exception
     */
    public function testWriteStream()
    {
        $adapter = $this->getBunnyCDNAdapter();

        $fileName = 'testing/testStream.txt';

        $tmpFile = tmpfile();
        fwrite($tmpFile, self::TEST_FILE_CONTENTS);
        rewind($tmpFile);
        self::assertIsArray(
            $adapter->writeStream($fileName, $tmpFile, new Config())
        );
        fclose($tmpFile);

        self::assertTrue(
            $adapter->delete($fileName)
        );
    }

    /**
     * @throws Exception
     */
    public function testReadStream()
    {
        $adapter = $this->getBunnyCDNAdapter();

        $fileName = 'testing/testStream.txt';

        self::assertIsArray(
            $adapter->write($fileName, self::TEST_FILE_CONTENTS, new Config())
        );

        $tmpFile = tmpfile();
        fwrite($tmpFile, self::TEST_FILE_CONTENTS);
        rewind($tmpFile);
        self::assertEquals(
            self::TEST_FILE_CONTENTS,
            stream_get_contents($adapter->readStream($fileName)['stream'])
        );
        fclose($tmpFile);

        self::assertTrue(
            $adapter->delete($fileName)
        );
    }

    /**
     * @note This is broken for directories, please only use on files
     *
     * @throws Exception
     */
    public function testCopy()
    {
        $adapter = $this->getBunnyCDNAdapter();

        self::assertIsArray(
            $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config())
        );

        self::assertTrue(
            $adapter->copy('testing/test.txt', 'testing/test_copied.txt'),
            'Copying a existing file doesn\'t work'
        );

        self::assertTrue(
            $adapter->has('testing/test_copied.txt')
        );

        self::assertFalse(
            $adapter->copy('notexisting/test.txt', 'notexisting/test_copied.txt'),
            'Copying a not existing file doesn\'t return false'
        );

        self::assertTrue(
            $adapter->delete('testing/test.txt')
        );

        self::assertTrue(
            $adapter->delete('testing/test_copied.txt')
        );
    }

    /**
     * @throws Exception
     */
    public function testListContents()
    {
        $adapter = $this->getBunnyCDNAdapter();
        self::assertIsArray(
            $adapter->listContents('/')
        );
        self::assertIsArray(
            $adapter->listContents('/')[0]
        );
    }

    /**
     * @throws Exception
     */
    public function testGetSize()
    {
        $adapter = $this->getBunnyCDNAdapter();

        self::assertIsArray(
            $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config())
        );

        self::assertIsNumeric(
            $adapter->getSize('testing/test.txt')['size']
        );

        self::assertTrue(
            $adapter->delete('testing/test.txt')
        );
    }

    /**
     * @throws Exception
     */
    public function testGetTimestamp()
    {
        $adapter = $this->getBunnyCDNAdapter();

        self::assertIsArray(
            $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config())
        );

        self::assertIsNumeric(
            $adapter->getTimestamp('testing/test.txt')['timestamp']
        );

        self::assertTrue(
            $adapter->delete('testing/test.txt')
        );
    }

    /**
     * @throws Exception
     */
    public function testRename()
    {
        $adapter = $this->getBunnyCDNAdapter();

        self::assertIsArray(
            $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config())
        );

        self::assertTrue(
            $adapter->rename('testing/test.txt', 'testing/test_renamed.txt')
        );

        self::assertFalse(
            $adapter->has('testing/test.txt')
        );

        self::assertTrue(
            $adapter->has('testing/test_renamed.txt')
        );

        self::assertTrue(
            $adapter->delete('testing/test_renamed.txt')
        );
    }

    /**
     * @throws Exception
     */
    public function testUpdate()
    {
        $adapter = $this->getBunnyCDNAdapter();

        self::assertIsArray(
            $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config())
        );

        self::assertIsArray(
            $adapter->update('testing/test.txt', self::TEST_FILE_CONTENTS . 'u', new Config())
        );

        self::assertEquals(
            self::TEST_FILE_CONTENTS . 'u',
            $adapter->read('/testing/test.txt')['contents']
        );
    }

    /**
     * @throws Exception
     */
    public function testUpdateStream()
    {
        $adapter = $this->getBunnyCDNAdapter();

        self::assertIsArray(
            $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config())
        );

        $tmpFile = tmpfile();
        fwrite($tmpFile, self::TEST_FILE_CONTENTS . 'u');
        rewind($tmpFile);
        self::assertIsArray(
            $adapter->updateStream('testing/test.txt', $tmpFile, new Config())
        );
        fclose($tmpFile);

        self::assertEquals(
            self::TEST_FILE_CONTENTS . 'u',
            $adapter->read('/testing/test.txt')['contents']
        );
    }

    /**
     * @throws Exception
     */
    public function testCreateDir()
    {
        $adapter = $this->getBunnyCDNAdapter();
        self::assertIsArray(
            $adapter->createDir('testing_created/', new Config())
        );

        self::assertTrue(
            $adapter->deleteDir('testing_created/')
        );
    }

    /**
     * @throws Exception
     */
    public function testTestsFlysystemCompatibility()
    {
        $adapter = $this->getBunnyCDNAdapter();
        $filesystem = new Filesystem($adapter);
        self::assertTrue($filesystem->createDir("test"));
        self::assertTrue($filesystem->deleteDir("test"));
    }

    /**
     * @throws Exception
     */
    public function testDelete()
    {
        $adapter = $this->getBunnyCDNAdapter();
        self::assertIsArray($adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config()));
        self::assertTrue($adapter->delete('testing/test.txt'));
        self::assertFalse($adapter->delete('testing/test.txtaaaaaa'));
    }

    /**
     * @throws Exception
     */
    public function testDeleteDir()
    {
        $adapter = $this->getBunnyCDNAdapter();
        self::assertIsArray($adapter->createDir('testing_for_deletion/',  new Config()));
        self::assertTrue($adapter->deleteDir('testing_for_deletion/'));
        self::assertTrue($adapter->deleteDir('testing/'));
    }
}
