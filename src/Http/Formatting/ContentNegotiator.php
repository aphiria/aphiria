<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\IHttpRequestMessage;

/**
 * Defines the default content negotiator
 */
class ContentNegotiator implements IContentNegotiator
{
    /** @const The default media type if none is found (RFC-2616) */
    private const DEFAULT_MEDIA_TYPE = 'application/octet-stream';
    /** @var MediaTypeFormatterMatcher The media type formatter matcher */
    private $mediaTypeFormatterMatcher;
    /** @var EncodingMatcher The encoding matcher */
    private $encodingMatcher;
    /** @var RequestHeaderParser The header parser */
    private $headerParser;

    /**
     * @param MediaTypeFormatterMatcher|null $mediaTypeFormatterMatcher The media type formatter matcher, or null if using the default one
     * @param EncodingMatcher|null $encodingMatcher The encoding matcher, or null if using the default one
     * @param RequestHeaderParser|null $headerParser The header parser, or null if using the default one
     */
    public function __construct(
        MediaTypeFormatterMatcher $mediaTypeFormatterMatcher = null,
        EncodingMatcher $encodingMatcher = null,
        RequestHeaderParser $headerParser = null
    ) {
        $this->mediaTypeFormatterMatcher = $mediaTypeFormatterMatcher ?? new MediaTypeFormatterMatcher();
        $this->encodingMatcher = $encodingMatcher ?? new EncodingMatcher();
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
    }

    /**
     * @inheritdoc
     */
    public function negotiateRequestContent(
        IHttpRequestMessage $request,
        array $mediaTypeFormatters
    ) : ?ContentNegotiationResult {
        if (count($mediaTypeFormatters) === 0) {
            throw new InvalidArgumentException('List of formatters cannot be empty');
        }

        $requestHeaders = $request->getHeaders();
        $contentTypeHeader = $this->headerParser->parseContentTypeHeader($requestHeaders);

        if ($contentTypeHeader === null) {
            // Default to the first registered media type formatter
            return new ContentNegotiationResult($mediaTypeFormatters[0], self::DEFAULT_MEDIA_TYPE, null);
        }

        $mediaTypeFormatterMatch = $this->mediaTypeFormatterMatcher->getBestMediaTypeFormatterMatch(
            $mediaTypeFormatters,
            [$contentTypeHeader]
        );

        if ($mediaTypeFormatterMatch === null) {
            return null;
        }

        $encoding = $this->encodingMatcher->getBestEncodingMatch(
            $mediaTypeFormatterMatch->getFormatter(),
            [],
            $mediaTypeFormatterMatch->getMediaTypeHeaderValue()
        );

        return new ContentNegotiationResult(
            $mediaTypeFormatterMatch->getFormatter(),
            $mediaTypeFormatterMatch->getMediaType(),
            $encoding
        );
    }

    /**
     * @inheritdoc
     */
    public function negotiateResponseContent(
        IHttpRequestMessage $request,
        array $mediaTypeFormatters
    ) : ?ContentNegotiationResult {
        if (count($mediaTypeFormatters) === 0) {
            throw new InvalidArgumentException('List of formatters cannot be empty');
        }

        $requestHeaders = $request->getHeaders();
        $acceptCharsetHeaders = $this->headerParser->parseAcceptCharsetHeader($requestHeaders);

        if (!$requestHeaders->containsKey('Accept')) {
            // Default to the first registered media type formatter
            $encoding = $this->encodingMatcher->getBestEncodingMatch($mediaTypeFormatters[0], $acceptCharsetHeaders, null);

            return new ContentNegotiationResult($mediaTypeFormatters[0], self::DEFAULT_MEDIA_TYPE, $encoding);
        }

        $mediaTypeHeaders = $this->headerParser->parseAcceptHeader($requestHeaders);
        $mediaTypeFormatterMatch = $this->mediaTypeFormatterMatcher->getBestMediaTypeFormatterMatch(
            $mediaTypeFormatters,
            $mediaTypeHeaders
        );

        if ($mediaTypeFormatterMatch === null) {
            return null;
        }

        $encoding = $this->encodingMatcher->getBestEncodingMatch(
            $mediaTypeFormatterMatch->getFormatter(),
            $acceptCharsetHeaders,
            $mediaTypeFormatterMatch->getMediaTypeHeaderValue()
        );

        return new ContentNegotiationResult(
            $mediaTypeFormatterMatch->getFormatter(),
            $mediaTypeFormatterMatch->getMediaType(),
            $encoding
        );
    }
}
