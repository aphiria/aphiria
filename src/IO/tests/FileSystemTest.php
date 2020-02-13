<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\IO\Tests;

use DateTime;
use Aphiria\IO\FileSystem;
use Aphiria\IO\FileSystemException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file system
 */
class FileSystemTest extends TestCase
{
    private FileSystem $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = new FileSystem();
    }

    protected function tearDown(): void
    {
        if (\file_exists(__DIR__ . '/test.txt')) {
            @unlink(__DIR__ . '/test.txt');
        }
    }

    public function testAppendingAddsToFile(): void
    {
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->fileSystem->append(__DIR__ . '/test.txt', ' bar');
        $this->assertEquals('foo bar', $this->fileSystem->read(__DIR__ . '/test.txt'));
    }

    public function testCopyingDirectoriesProperlyCopiesThem(): void
    {
        $this->fileSystem->copyDirectory(__DIR__ . '/files/subdirectory', __DIR__ . '/tmp');
        $this->assertTrue($this->fileSystem->exists(__DIR__ . '/tmp/foo.txt'));
        $this->assertTrue($this->fileSystem->exists(__DIR__ . '/tmp/subdirectory/bar.txt'));
        @unlink(__DIR__ . '/tmp/subdirectory/bar.txt');
        @rmdir(__DIR__ . '/tmp/subdirectory');
        @unlink(__DIR__ . '/tmp/foo.txt');
        @rmdir(__DIR__ . '/tmp');
    }

    public function testCopyingDirectoryThatIsNotADirectoryReturnsFalse(): void
    {
        $this->assertFalse($this->fileSystem->copyDirectory(__DIR__ . '/doesnotexist', __DIR__ . '/tmp'));
    }

    public function testCopyingFileCopiesTheContents(): void
    {
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->assertTrue($this->fileSystem->copyFile(__DIR__ . '/test.txt', __DIR__ . '/test2.txt'));
        $this->assertEquals('foo', $this->fileSystem->read(__DIR__ . '/test2.txt'));
        @unlink(__DIR__ . '/test2.txt');
    }

    public function testDeletingDirectoryActuallyDeletesIt(): void
    {
        @mkdir(__DIR__ . '/tmp');
        @mkdir(__DIR__ . '/tmp/subdirectory');
        file_put_contents(__DIR__ . '/tmp/subdirectory/foo.txt', 'bar');
        $this->fileSystem->deleteDirectory(__DIR__ . '/tmp');
        $this->assertFalse($this->fileSystem->exists(__DIR__ . '/tmp'));

        // Just in case, remove the structure
        foreach (['/tmp/subdirectory/foo.txt', '/tmp/subdirectory', '/tmp'] as $relativePath) {
            if (\file_exists(__DIR__ . $relativePath)) {
                @\unlink(__DIR__ . $relativePath);
            }
        }
    }

    public function testDeletingFileActuallyDeletesIt(): void
    {
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->assertTrue($this->fileSystem->deleteFile(__DIR__ . '/test.txt'));
        $this->assertFalse($this->fileSystem->exists(__DIR__ . '/test.txt'));
    }

    public function testExistsReturnsWhetherOrNotFilesAndDirectoryActuallyExist(): void
    {
        $this->assertTrue($this->fileSystem->exists(__DIR__));
        $this->assertFalse($this->fileSystem->exists(__DIR__ . '/doesnotexist'));
        $this->assertTrue($this->fileSystem->exists(__FILE__));
        $this->assertFalse($this->fileSystem->exists(__DIR__ . '/doesnotexist.txt'));
    }

    public function testGettingBasenameForInvalidFileThrowsException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->fileSystem->getBasename(__DIR__ . '/doesnotexist.txt');
    }

    public function testGettingBasenameForValidFileReturnsBasename(): void
    {
        $this->assertEquals('foo.txt', $this->fileSystem->getBasename(__DIR__ . '/files/subdirectory/foo.txt'));
    }

    public function testGettingDirectoriesWithRecursionFindsAllDirectories(): void
    {
        $this->assertEquals([
            __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'subdirectory',
            __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'subdirectory' . DIRECTORY_SEPARATOR . 'subdirectory'
        ], $this->fileSystem->getDirectories(__DIR__ . DIRECTORY_SEPARATOR . 'files', true));
    }

    public function testGettingDirectoriesWithoutRecursionAtTopLevel(): void
    {
        $this->assertEquals(
            [__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'subdirectory'],
            $this->fileSystem->getDirectories(__DIR__ . DIRECTORY_SEPARATOR . 'files')
        );
    }

    public function testGettingDirectoryNameForInvalidDirectoryThrowsException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->fileSystem->getDirectoryName(__DIR__ . '/doesnotexist.txt');
    }

    public function testGettingDirectoryNameForValidDirectoryReturnsName(): void
    {
        $this->assertEquals(
            __DIR__ . '/files/subdirectory',
            $this->fileSystem->getDirectoryName(__DIR__ . '/files/subdirectory/foo.txt')
        );
    }

    public function testGettingExtensionForInvalidFileThrowsException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->fileSystem->getExtension(__DIR__ . '/doesnotexist.txt');
    }

    public function testGettingExtensionForValidFileReturnsCorrectExtension(): void
    {
        $this->assertEquals('txt', $this->fileSystem->getExtension(__DIR__ . '/files/subdirectory/foo.txt'));
    }

    public function testGettingFileNameForInvalidFileThrowsException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->fileSystem->getFileName(__DIR__ . '/doesnotexist.txt');
    }

    public function testGettingFileNameForValidFileReturnsFileName(): void
    {
        $this->assertEquals('foo', $this->fileSystem->getFileName(__DIR__ . '/files/subdirectory/foo.txt'));
    }

    public function testGettingFileSizeForInvalidFileThrowsException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->fileSystem->getFileSize(__DIR__ . '/doesnotexist.txt');
    }

    public function testGettingFileSizeForValidFileReturnsFileSize(): void
    {
        $path = __DIR__ . '/files/subdirectory/foo.txt';
        $this->assertEquals(filesize($path), $this->fileSystem->getFileSize($path));
    }

    public function testGettingFilesWithGlobFindsFilesThatMatchPattern(): void
    {
        $this->assertCount(0, array_diff([
                __DIR__ . '/files/subdirectory/foo.txt'
            ], $this->fileSystem->glob(__DIR__ . '/files/subdirectory/*.txt')));
    }

    public function testGettingFilesWithInvalidPathReturnsEmptyArray(): void
    {
        $this->assertEquals([], $this->fileSystem->getFiles(__FILE__));
    }

    public function testGettingFilesWithRecursionFindsAllNestedFiles(): void
    {
        $this->assertCount(0, array_diff([
                __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'subdirectory' . DIRECTORY_SEPARATOR . 'subdirectory' . DIRECTORY_SEPARATOR . 'bar.txt',
                __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'subdirectory' . DIRECTORY_SEPARATOR . 'foo.txt',
                __FILE__
            ], $this->fileSystem->getFiles(__DIR__, true)));
    }

    public function testGettingFilesWithoutRecursionFindsFilesAtTopLevel(): void
    {
        $this->assertEquals([__FILE__], $this->fileSystem->getFiles(__DIR__));
    }

    public function testGettingLastModifiedTimeForInvalidFileThrowsException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->fileSystem->getLastModified(__DIR__ . '/doesnotexist.txt');
    }

    public function testGettingLastModifiedTimeForValidFileReturnsLastModifiedTime(): void
    {
        $path = __DIR__ . '/files/subdirectory/foo.txt';
        $this->assertEquals(
            DateTime::createFromFormat('U', (string)filemtime($path)),
            $this->fileSystem->getLastModified($path)
        );
    }

    public function testIsDirectoryOnInvalidDirectoryReturnsFalse(): void
    {
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->assertFalse($this->fileSystem->isDirectory(__DIR__ . '/test.txt'));
    }

    /**
     * Tests checking if an invalid file is not a file
     */
    public function testIsFileOnInvalidFileReturnsFalse(): void
    {
        $this->assertFalse($this->fileSystem->isFile(__DIR__));
    }

    public function testIsReadableReturnsWhetherOrNotFileIsReadable(): void
    {
        $this->assertFalse($this->fileSystem->isReadable(__DIR__ . '/doesnotexist.txt'));
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->assertTrue($this->fileSystem->isReadable(__DIR__ . '/test.txt'));
    }

    public function testIsWritableReturnsWhetherOrNotFileIsWritable(): void
    {
        $this->assertFalse($this->fileSystem->isWritable(__DIR__ . '/doesnotexist.txt'));
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->assertTrue($this->fileSystem->isWritable(__DIR__ . '/test.txt'));
    }

    public function testMakingDirectoryActuallyCreatesADirectory(): void
    {
        $this->assertTrue($this->fileSystem->makeDirectory(__DIR__ . '/tmp'));
        $this->assertTrue($this->fileSystem->exists(__DIR__ . '/tmp'));
        rmdir(__DIR__ . '/tmp');
    }

    public function testMovingFileActuallyMovesTheFile(): void
    {
        file_put_contents(__DIR__ . '/test.txt', 'foo bar');
        $this->assertTrue($this->fileSystem->move(__DIR__ . '/test.txt', __DIR__ . '/test2.txt'));
        $this->assertFalse($this->fileSystem->exists(__DIR__ . '/test.txt'));
        $this->assertTrue($this->fileSystem->exists(__DIR__ . '/test2.txt'));
        $this->assertEquals('foo bar', $this->fileSystem->read(__DIR__ . '/test2.txt'));
        @unlink(__DIR__ . '/test2.txt');
    }

    public function testReadingFileThatDoesNotExistThrowsException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->assertEquals('foo', $this->fileSystem->read(__DIR__ . '/doesnotexist.txt'));
    }

    public function testReadingFileThatExistsReturnsItsContents(): void
    {
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->assertEquals('foo', $this->fileSystem->read(__DIR__ . '/test.txt'));
    }

    public function testValidDirectoryIsDirectoryReturnsTrue(): void
    {
        $this->assertTrue($this->fileSystem->isDirectory(__DIR__));
    }

    public function testValidFileIsFileReturnsTrue(): void
    {
        file_put_contents(__DIR__ . '/test.txt', 'foo');
        $this->assertTrue($this->fileSystem->isFile(__DIR__ . '/test.txt'));
    }

    public function testWritingToFileReturnsNumberOfBytesWrittenAndWritesContents(): void
    {
        $this->assertTrue(is_numeric($this->fileSystem->write(__DIR__ . '/test.txt', 'foo bar')));
        $this->assertEquals('foo bar', $this->fileSystem->read(__DIR__ . '/test.txt'));
    }
}
