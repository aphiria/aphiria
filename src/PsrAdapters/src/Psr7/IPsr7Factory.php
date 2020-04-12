<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Psr7;

use Aphiria\IO\Streams\IStream;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Defines the interface for PSR-7 factories to implement
 */
interface IPsr7Factory
{
    /**
     * Creates an Aphiria request from a PSR-7 request
     *
     * @param ServerRequestInterface $psr7Request The PSR-7 request to create an Aphiria request from
     * @return IHttpRequestMessage The Aphiria request
     */
    public function createAphiriaRequest(ServerRequestInterface $psr7Request): IHttpRequestMessage;

    /**
     * Creates an Aphiria response from a PSR-7 response
     *
     * @param ResponseInterface $psr7Response The PSR-7 response to create an Aphiria response from
     * @return IHttpResponseMessage The Aphiria response
     */
    public function createAphiriaResponse(ResponseInterface $psr7Response): IHttpResponseMessage;

    /**
     * Creates an Aphiria stream from a PSR-7 stream
     *
     * @param StreamInterface $psr7Stream The PSR-7 stream to create an Aphiria stream from
     * @return IStream The Aphiria stream
     */
    public function createAphiriaStream(StreamInterface $psr7Stream): IStream;

    /**
     * Creates an Aphiria URI from a PSR-7 URI
     *
     * @param UriInterface $psr7Uri The PSR-7 URI to create an Aphiria URI from
     * @return Uri The Aphiria URI
     */
    public function createAphiriaUri(UriInterface $psr7Uri): Uri;

    /**
     * Creates a PSR-7 request from an Aphiria request
     *
     * @param IHttpRequestMessage $aphiriaRequest The Aphiria request to create a PSR-7 request from
     * @return ServerRequestInterface The PSR-7 request
     */
    public function createPsr7Request(IHttpRequestMessage $aphiriaRequest): ServerRequestInterface;

    /**
     * Creates a PSR-7 request from an Aphiria response
     *
     * @param IHttpResponseMessage $aphiriaResponse The Aphiria response to create a PSR-7 response from
     * @return ResponseInterface The PSR-7 response
     */
    public function createPsr7Response(IHttpResponseMessage $aphiriaResponse): ResponseInterface;

    /**
     * Creates a PSR-7 stream from an Aphiria stream
     *
     * @param IStream $stream The Aphiria stream
     * @return StreamInterface The PSR-7 stream
     */
    public function createPsr7Stream(IStream $stream): StreamInterface;

    /**
     * Creates a mapping of PSR-7 uploaded files from an Aphiria request
     *
     * @param IHttpRequestMessage $aphiriaRequest The Aphiria request
     * @return UploadedFileInterface[] The mapping of file names to file instances
     */
    public function createPsr7UploadedFiles(IHttpRequestMessage $aphiriaRequest): array;

    /**
     * Creates a PSR-7 URI from an Aphiria URI
     *
     * @param Uri $aphiriaUri The Aphiria URI to create a PSR-7 URI from
     * @return UriInterface The PSR-7 URI
     */
    public function createPsr7Uri(Uri $aphiriaUri): UriInterface;
}
