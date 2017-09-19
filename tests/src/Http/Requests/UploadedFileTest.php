<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Tests\Net\Http\Requests\Mocks\UploadedFile as MockUploadedFile;
use RuntimeException;

/**
 * Tests the uploaded file
 */
class UploadedFileTest extends \PHPUnit\Framework\TestCase
{
    /** The uploaded file's filename */
    const UPLOADED_FILE_FILENAME = __DIR__ . '/files/UploadedFile.txt';
    /** The temporary file's filename */
    const TEMP_FILENAME = __DIR__ . '/files/TempFile.txt';
    /** @var MockUploadedFile The uploaded file to use in tests */
    private $file = null;

    /**
     * Tears down the class
     */
    public static function tearDownAfterClass() : void
    {
        $files = glob(__DIR__ . '/tmp/*');

        foreach ($files as $file) {
            unlink($file);
        }

        if (file_exists(__DIR__ . '/tmp')) {
            rmdir(__DIR__ . '/tmp');
        }
    }

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->file = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100,
            'text/plain',
            UPLOAD_ERR_OK
        );
    }

    /**
     * Tests checking for errors
     */
    public function testCheckingForErrors() : void
    {
        $validFile = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100,
            'text/plain',
            UPLOAD_ERR_OK
        );
        $this->assertFalse($validFile->hasErrors());
        $invalidFile = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100,
            'text/plain',
            UPLOAD_ERR_EXTENSION
        );
        $this->assertTrue($invalidFile->hasErrors());
    }

    /**
     * Tests getting the client extension
     */
    public function testGettingClientExtension() : void
    {
        $this->assertEquals('txt', $this->file->getClientExtension());
    }

    /**
     * Tests getting the client filename
     */
    public function testGettingClientFilename() : void
    {
        $this->assertEquals(self::TEMP_FILENAME, $this->file->getClientFilename());
    }

    /**
     * Tests getting the client mime type
     */
    public function testGettingClientMimeType() : void
    {
        $this->assertEquals('text/plain', $this->file->getClientMimeType());
    }

    /**
     * Tests getting the client size
     */
    public function testGettingClientSize() : void
    {
        $this->assertEquals(100, $this->file->getClientSize());
    }

    /**
     * Tests getting the default error
     */
    public function testGettingDefaultError() : void
    {
        $file = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100
        );
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
    }

    /**
     * Tests getting the default temp mime type
     */
    public function testGettingDefaultTempMimeType() : void
    {
        $file = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100
        );
        $this->assertEmpty($file->getClientMimeType());
    }

    /**
     * Tests getting the error
     */
    public function testGettingError() : void
    {
        $file = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100,
            'text/plain',
            UPLOAD_ERR_EXTENSION
        );
        $this->assertEquals(UPLOAD_ERR_EXTENSION, $file->getError());
    }

    /**
     * Tests getting the mime type
     */
    public function testGettingMimeType() : void
    {
        $file = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100,
            'foo/bar'
        );
        $this->assertEquals('text/plain', $file->getMimeType());
    }

    /**
     * Tests getting the path
     */
    public function testGettingPath() : void
    {
        $this->assertEquals(__DIR__ . '/files', $this->file->getPath());
    }

    /**
     * Tests moving the file
     */
    public function testMovingFile() : void
    {
        // Test specifying directory for target and a filename
        $this->file->move(__DIR__ . '/tmp', 'bar.txt');
        $this->assertEquals('bar', file_get_contents(__DIR__ . '/tmp/bar.txt'));
        // Test not specifying a name
        $this->file->move(__DIR__ . '/tmp');
        $this->assertEquals('bar', file_get_contents(__DIR__ . '/tmp/UploadedFile.txt'));
    }

    /**
     * Tests moving a file with errors
     */
    public function testMovingFileWithErrors() : void
    {
        $this->expectException(RuntimeException::class);
        $file = new MockUploadedFile(
            self::UPLOADED_FILE_FILENAME,
            self::TEMP_FILENAME,
            100,
            'text/plain',
            UPLOAD_ERR_EXTENSION
        );
        $file->move(__DIR__ . '/tmp', 'foo.txt');
    }

    /**
     * Tests reading as a stream creates a stream for the source file
     */
    public function testReadingAsStreamCreatesStreamForSourceFile() : void
    {
        $expectedContents = file_get_contents(self::UPLOADED_FILE_FILENAME);
        $this->assertEquals($expectedContents, (string)$this->file->readAsStream());
    }

    /**
     * Tests reading as a stream returns the same instance every time
     */
    public function testReadingAsStreamReturnsTheSameInstanceEveryTime() : void
    {
        $expectedStream = $this->file->readAsStream();
        $this->assertSame($expectedStream, $this->file->readAsStream());
    }
}
