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
use Opulence\Net\Http\Headers\AcceptCharsetHeaderValue;
use Opulence\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;
use Opulence\Net\Http\Headers\IHeaderValueWithQualityScore;
use Opulence\Net\Http\Headers\MediaTypeHeaderValue;
use Opulence\Net\Http\IHttpRequestMessage;

/**
 * Defines the default content negotiator
 */
class ContentNegotiator implements IContentNegotiator
{
    /** @const The default media type if none is found (RFC-2616) */
    private const DEFAULT_MEDIA_TYPE = 'application/octet-stream';
    /** @var IMediaTypeFormatter[] The list of registered formatters */
    private $formatters;
    /** @var RequestHeaderParser The header parser */
    private $headerParser;

    /**
     * @param IMediaTypeFormatter[] $formatters The list of formatters
     * @param RequestHeaderParser|null $headerParser The header parser, or null if using the default one
     * @throws InvalidArgumentException Thrown if the list of formatters is empty
     */
    public function __construct(array $formatters, RequestHeaderParser $headerParser = null)
    {
        if (count($formatters) === 0) {
            throw new InvalidArgumentException('List of formatters must not be empty');
        }

        $this->formatters = $formatters;
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
    }

    /**
     * @inheritdoc
     */
    public function negotiateRequestContent(IHttpRequestMessage $request) : ?ContentNegotiationResult
    {
        $requestHeaders = $request->getHeaders();

        if (!$requestHeaders->containsKey('Content-Type')) {
            // Default to the first registered media type formatter
            return new ContentNegotiationResult($this->formatters[0], self::DEFAULT_MEDIA_TYPE, null);
        }

        $contentTypeHeaderParameters = $this->headerParser->parseParameters($requestHeaders, 'Content-Type', 0);
        // The first value should be the content-type
        $contentType = $contentTypeHeaderParameters->getKeys()[0];
        $contentTypeHeader = new ContentTypeHeaderValue($contentType, $contentTypeHeaderParameters);
        $mediaTypeFormatterMatch = $this->getBestMediaTypeFormatterMatch($contentTypeHeader);

        if ($mediaTypeFormatterMatch !== null) {
            $encoding = $this->getBestEncoding(
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

        return null;
    }

    /**
     * @inheritdoc
     */
    public function negotiateResponseContent(IHttpRequestMessage $request) : ?ContentNegotiationResult
    {
        $requestHeaders = $request->getHeaders();
        $acceptCharsetHeaders = $this->headerParser->parseAcceptCharsetHeader($requestHeaders);
        $rankedAcceptCharsetHeaders = $this->rankCharsetHeaders($acceptCharsetHeaders);

        if (!$requestHeaders->containsKey('Accept')) {
            // Default to the first registered media type formatter
            $encoding = $this->getBestEncoding($this->formatters[0], $rankedAcceptCharsetHeaders);

            return new ContentNegotiationResult($this->formatters[0], self::DEFAULT_MEDIA_TYPE, $encoding);
        }

        $mediaTypeHeaders = $this->headerParser->parseAcceptHeader($requestHeaders);
        $rankedMediaTypeHeaders = $this->rankMediaTypeHeaders($mediaTypeHeaders);

        foreach ($rankedMediaTypeHeaders as $mediaTypeHeader) {
            $mediaTypeFormatterMatch = $this->getBestMediaTypeFormatterMatch($mediaTypeHeader);

            if ($mediaTypeFormatterMatch !== null) {
                $encoding = $this->getBestEncoding(
                    $mediaTypeFormatterMatch->getFormatter(),
                    $rankedAcceptCharsetHeaders,
                    $mediaTypeFormatterMatch->getMediaTypeHeaderValue()
                );

                return new ContentNegotiationResult(
                    $mediaTypeFormatterMatch->getFormatter(),
                    $mediaTypeFormatterMatch->getMediaType(),
                    $encoding
                );
            }
        }

        return null;
    }

    /**
     * Compares two charsets and returns which of them is "lower" than the other
     *
     * @param AcceptCharsetHeaderValue $a The first charset header to compare
     * @param AcceptCharsetHeaderValue $b The second charset header to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    protected function compareCharsets(AcceptCharsetHeaderValue $a, AcceptCharsetHeaderValue $b) : int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aValue = $a->getCharset();
        $bValue = $b->getCharset();

        if ($aValue === '*') {
            if ($bValue === '*') {
                return 0;
            }

            return 1;
        }

        if ($bValue === '*') {
            return -1;
        }

        return 0;
    }

    /**
     * Compares two media types and returns which of them is "lower" than the other
     *
     * @param AcceptMediaTypeHeaderValue $a The first media type to compare
     * @param AcceptMediaTypeHeaderValue $b The second media type to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    protected function compareMediaTypes(AcceptMediaTypeHeaderValue $a, AcceptMediaTypeHeaderValue $b) : int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aType = $a->getType();
        $bType = $b->getType();
        $aSubType = $a->getSubType();
        $bSubType = $b->getSubType();

        if ($aType === '*') {
            if ($bType === '*') {
                return 0;
            }

            return 1;
        }

        if ($aSubType === '*') {
            if ($bSubType === '*') {
                return 0;
            }

            return 1;
        }

        // If we've gotten here, then $a had no wildcards
        if ($bType === '*' || $bSubType === '*') {
            return -1;
        }

        return 0;
    }

    /**
     * Filters out any header values with a zero quality score
     *
     * @param IHeaderValueWithQualityScore $headerValue The value to check
     * @return bool True if we should keep the value, otherwise false
     */
    protected function filterZeroScores(IHeaderValueWithQualityScore $headerValue) : bool
    {
        return $headerValue->getQuality() > 0;
    }

    /**
     * Gets the best character encoding for the input media type formatter
     *
     * @param IMediaTypeFormatter $formatter The media type formatter to match against
     * @param AcceptCharsetHeaderValue[] $rankedAcceptCharsetHeaders The ranked list of charset header values to match against
     * @param MediaTypeHeaderValue|null $mediaTypeHeader The matched media type header value
     * @return string|null The best charset if one was found, otherwise null
     */
    protected function getBestEncoding(
        IMediaTypeFormatter $formatter,
        array $rankedAcceptCharsetHeaders,
        MediaTypeHeaderValue $mediaTypeHeader = null
    ) : ?string {
        foreach ($rankedAcceptCharsetHeaders as $acceptCharsetHeader) {
            foreach ($formatter->getSupportedEncodings() as $supportedEncoding) {
                if (
                    $acceptCharsetHeader->getCharset() === '*'
                    || $acceptCharsetHeader->getCharset() === $supportedEncoding
                ) {
                    return $supportedEncoding;
                }
            }
        }

        if ($mediaTypeHeader === null || $mediaTypeHeader->getCharset() === null) {
            return null;
        }

        // Fall back to the charset in the media type header
        foreach ($formatter->getSupportedEncodings() as $supportedEncoding) {
            if ($mediaTypeHeader->getCharset() === '*' || $mediaTypeHeader->getCharset() === $supportedEncoding) {
                return $supportedEncoding;
            }
        }

        return null;
    }

    /**
     * Gets the best media type formatter match
     *
     * @param MediaTypeHeaderValue $mediaTypeHeader The media type header value to match on
     * @return MediaTypeFormatterMatch|null The best media type formatter match if one was found, otherwise null
     * @throws InvalidArgumentException Thrown if the media type was incorrectly formatted
     */
    protected function getBestMediaTypeFormatterMatch(MediaTypeHeaderValue $mediaTypeHeader) : ?MediaTypeFormatterMatch
    {
        $mediaTypeParts = explode('/', $mediaTypeHeader->getMediaType());

        // Don't bother going on if the media type isn't in the correct format
        if (count($mediaTypeParts) !== 2 || $mediaTypeParts[0] === '' || $mediaTypeParts[1] === '') {
            throw new InvalidArgumentException('Media type must be in format {type}/{sub-type}');
        }

        [$type, $subType] = $mediaTypeParts;

        foreach ($this->formatters as $formatter) {
            foreach ($formatter->getSupportedMediaTypes() as $supportedMediaType) {
                [$supportedType, $supportedSubType] = explode('/', $supportedMediaType);

                // Checks if the type is a wildcard or a match and the sub-type is a wildcard or a match
                if (
                    $type === '*' ||
                    ($subType === '*' && $type === $supportedType) ||
                    ($type === $supportedType && $subType === $supportedSubType)
                ) {
                    return new MediaTypeFormatterMatch($formatter, $supportedMediaType, $mediaTypeHeader);
                }
            }
        }

        return null;
    }

    /**
     * Ranks the charset headers by quality, and then by specificity
     *
     * @param AcceptCharsetHeaderValue[] $charsetHeaders The list of charset headers to rank
     * @return AcceptCharsetHeaderValue[] The ranked list of charset headers
     */
    protected function rankCharsetHeaders(array $charsetHeaders) : array
    {
        usort($charsetHeaders, [$this, 'compareCharsets']);
        $rankedCharsetHeaders = array_filter($charsetHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter()
        return array_values($rankedCharsetHeaders);
    }

    /**
     * Ranks the media type headers by quality, and then by specificity
     *
     * @param AcceptMediaTypeHeaderValue[] $mediaTypeHeaders The list of media type headers to rank
     * @return AcceptMediaTypeHeaderValue[] The ranked list of media type headers
     */
    protected function rankMediaTypeHeaders(array $mediaTypeHeaders) : array
    {
        usort($mediaTypeHeaders, [$this, 'compareMediaTypes']);
        $rankedMediaTypeHeaders = array_filter($mediaTypeHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter()
        return array_values($rankedMediaTypeHeaders);
    }
}
