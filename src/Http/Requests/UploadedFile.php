<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use finfo;
use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;
use RuntimeException;
use SplFileInfo;

/**
 * Defines an uploaded file
 */
class UploadedFile extends SplFileInfo
{
    /** @var string The client name of the file */
    private $clientFilename = '';
    /** @var int The client size of the file in bytes */
    private $clientSize = 0;
    /** @var string The client mime type of the file */
    private $clientMimeType = '';
    /** @var int The error message, if there was any */
    private $error = UPLOAD_ERR_OK;
    /** @var IStream The uploaded file as a stream */
    private $stream = null;

    /**
     * @param string $path The path to the file
     * @param string $clientFilename The client filename
     * @param int $clientSize The client size of the temporary file in bytes
     * @param string $clientMimeType The client mime type of the temporary file
     * @param int $error The error message, if there was any
     */
    public function __construct(
        string $path,
        string $clientFilename,
        int $clientSize,
        string $clientMimeType = '',
        int $error = UPLOAD_ERR_OK
    ) {
        parent::__construct($path);

        $this->clientFilename = $clientFilename;
        $this->clientSize = $clientSize;
        $this->clientMimeType = $clientMimeType;
        $this->error = $error;
    }

    /**
     * Gets the client (untrusted) file's extension
     *
     * @return string The client (untrusted) file's extension
     */
    public function getClientExtension() : string
    {
        return pathinfo($this->clientFilename, PATHINFO_EXTENSION);
    }

    /**
     * Gets the client (untrusted) filename
     *
     * @return string The client (untrusted) filename
     */
    public function getClientFilename() : string
    {
        return $this->clientFilename;
    }

    /**
     * Gets the client (untrusted) mime type
     *
     * @return string The client (untrusted) mime type
     */
    public function getClientMimeType() : string
    {
        return $this->clientMimeType;
    }

    /**
     * Gets the client (untrusted) size
     *
     * @return int The client (untrusted) size
     */
    public function getClientSize() : int
    {
        return $this->clientSize;
    }

    /**
     * Gets the error code
     *
     * @return int The error code
     */
    public function getError() : int
    {
        return $this->error;
    }

    /**
     * Gets the actual mime type of the file
     *
     * @return string The actual mime type
     */
    public function getMimeType() : string
    {
        $fInfo = new finfo(FILEINFO_MIME_TYPE);

        return $fInfo->file($this->getPathname());
    }

    /**
     * Gets whether or not this file has errors
     *
     * @return bool True if the file has errors, otherwise false
     */
    public function hasErrors() : bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

    /**
     * Moves the file to the target path
     *
     * @param string $targetDirectory The target directory
     * @param string|null $name The new name
     * @throws RuntimeException Thrown if the file could not be moved
     */
    public function move(string $targetDirectory, string $name = null)
    {
        if ($this->hasErrors()) {
            throw new RuntimeException('Cannot move file with errors');
        }

        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0777, true)) {
                throw new RuntimeException('Could not create directory ' . $targetDirectory);
            }
        } elseif (!is_writable($targetDirectory)) {
            throw new RuntimeException($targetDirectory . ' is not writable');
        }

        $targetPath = rtrim($targetDirectory, '\\/') . '/' . ($name ?: $this->getBasename());
        $this->doMove($targetPath);
    }

    /**
     * Reads the uploaded file as a stream
     * This stream can be copied to the desired source directory or CDN
     *
     * @return IStream The uploaded file as a stream
     */
    public function readAsStream() : IStream
    {
        if ($this->stream !== null) {
            return $this->stream;
        }

        $this->stream = new Stream(fopen($this->getPathname(), 'r'));

        return $this->stream;
    }

    /**
     * Moves a file from one location to another
     * This is split into its own method so that it can be overridden for testing purposes
     *
     * @param string $target The path to move to
     * @throws RuntimeException Thrown if the file could not be moved
     */
    protected function doMove(string $target) : void
    {
        $destinationStream = new Stream(fopen($target, 'w+'));
        $this->readAsStream()->copyToStream($destinationStream);
    }
}
