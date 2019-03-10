<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\ContentNegotiation;

use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\IHttpRequestMessage;
use function array_unique;
use function count;
use InvalidArgumentException;

/**
 * Defines the default content negotiator
 */
final class ContentNegotiator implements IContentNegotiator
{
    /** @const The default media type if none is found (RFC 7231) */
    private const DEFAULT_REQUEST_MEDIA_TYPE = 'application/octet-stream';
    /** @var IMediaTypeFormatter[] The list of media type formatters */
    private $mediaTypeFormatters;
    /** @var array The list of supported languages */
    private $supportedLanguages;
    /** @var MediaTypeFormatterMatcher The media type formatter matcher */
    private $mediaTypeFormatterMatcher;
    /** @var EncodingMatcher The encoding matcher */
    private $encodingMatcher;
    /** @var LanguageMatcher The language matcher */
    private $languageMatcher;
    /** @var RequestHeaderParser The header parser */
    private $headerParser;

    /**
     * @param IMediaTypeFormatter[] $mediaTypeFormatters The list of media type formatters to use
     * @param array $supportedLanguages The list of supported languages
     * @param MediaTypeFormatterMatcher|null $mediaTypeFormatterMatcher The media type formatter matcher, or null if using the default one
     * @param EncodingMatcher|null $encodingMatcher The encoding matcher, or null if using the default one
     * @param LanguageMatcher|null $languageMatcher The language matcher, or null if using the default one
     * @param RequestHeaderParser|null $headerParser The header parser, or null if using the default one
     * @throws InvalidArgumentException Thrown if the list of media type formatters is empty
     */
    public function __construct(
        array $mediaTypeFormatters,
        array $supportedLanguages = [],
        MediaTypeFormatterMatcher $mediaTypeFormatterMatcher = null,
        EncodingMatcher $encodingMatcher = null,
        LanguageMatcher $languageMatcher = null,
        RequestHeaderParser $headerParser = null
    ) {
        if (count($mediaTypeFormatters) === 0) {
            throw new InvalidArgumentException('List of formatters cannot be empty');
        }

        $this->mediaTypeFormatters = $mediaTypeFormatters;
        $this->supportedLanguages = $supportedLanguages;
        $this->mediaTypeFormatterMatcher = $mediaTypeFormatterMatcher ?? new MediaTypeFormatterMatcher();
        $this->encodingMatcher = $encodingMatcher ?? new EncodingMatcher();
        $this->languageMatcher = $languageMatcher ?? new LanguageMatcher();
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
    }

    /**
     * @inheritdoc
     */
    public function getAcceptableResponseMediaTypes(string $type): array
    {
        $acceptableMediaTypes = [];

        foreach ($this->mediaTypeFormatters as $mediaTypeFormatter) {
            if ($mediaTypeFormatter->canWriteType($type)) {
                $acceptableMediaTypes = array_merge(
                    $acceptableMediaTypes,
                    $mediaTypeFormatter->getSupportedMediaTypes()
                );
            }
        }

        return array_unique($acceptableMediaTypes);
    }

    /**
     * @inheritdoc
     */
    public function negotiateRequestContent(string $type, IHttpRequestMessage $request): ContentNegotiationResult
    {
        $requestHeaders = $request->getHeaders();
        $contentTypeHeader = $this->headerParser->parseContentTypeHeader($requestHeaders);
        $language = null;
        $requestHeaders->tryGetFirst('Content-Language', $language);

        if ($contentTypeHeader === null) {
            // We cannot negotiate the request content
            return new ContentNegotiationResult(null, self::DEFAULT_REQUEST_MEDIA_TYPE, null, $language);
        }

        $mediaTypeFormatterMatch = $this->mediaTypeFormatterMatcher->getBestRequestMediaTypeFormatterMatch(
            $type,
            $this->mediaTypeFormatters,
            $contentTypeHeader
        );

        if ($mediaTypeFormatterMatch === null) {
            return new ContentNegotiationResult(null, null, null, $language);
        }

        $encoding = $this->encodingMatcher->getBestEncodingMatch(
            $mediaTypeFormatterMatch->getFormatter(),
            [],
            $mediaTypeFormatterMatch->getMediaTypeHeaderValue()
        );

        return new ContentNegotiationResult(
            $mediaTypeFormatterMatch->getFormatter(),
            $mediaTypeFormatterMatch->getMediaType(),
            $encoding,
            $language
        );
    }

    /**
     * @inheritdoc
     */
    public function negotiateResponseContent(string $type, IHttpRequestMessage $request): ContentNegotiationResult
    {
        $requestHeaders = $request->getHeaders();
        $acceptCharsetHeaders = $this->headerParser->parseAcceptCharsetHeader($requestHeaders);
        $acceptLanguageHeaders = $this->headerParser->parseAcceptLanguageHeader($requestHeaders);
        $language = $this->languageMatcher->getBestLanguageMatch($this->supportedLanguages, $acceptLanguageHeaders);

        if (!$requestHeaders->containsKey('Accept')) {
            return $this->createDefaultResponseContentNegotiationResult($type, $language, $acceptCharsetHeaders);
        }

        $mediaTypeHeaders = $this->headerParser->parseAcceptHeader($requestHeaders);
        $mediaTypeFormatterMatch = $this->mediaTypeFormatterMatcher->getBestResponseMediaTypeFormatterMatch(
            $type,
            $this->mediaTypeFormatters,
            $mediaTypeHeaders
        );

        if ($mediaTypeFormatterMatch === null) {
            return new ContentNegotiationResult(null, null, null, $language);
        }

        $encoding = $this->encodingMatcher->getBestEncodingMatch(
            $mediaTypeFormatterMatch->getFormatter(),
            $acceptCharsetHeaders,
            $mediaTypeFormatterMatch->getMediaTypeHeaderValue()
        );

        return new ContentNegotiationResult(
            $mediaTypeFormatterMatch->getFormatter(),
            $mediaTypeFormatterMatch->getMediaType(),
            $encoding,
            $language
        );
    }

    /**
     * Creates the default content negotiation result in case no Accept header was specified
     *
     * @param string $type The type to negotiate
     * @param string|null $language The selected language
     * @param AcceptCharsetHeaderValue[] $acceptCharsetHeaders The list of Accept-Charset headers
     * @return ContentNegotiationResult The content negotiation result
     */
    private function createDefaultResponseContentNegotiationResult(
        string $type,
        ?string $language,
        array $acceptCharsetHeaders
    ): ContentNegotiationResult {
        // Default to the first registered media type formatter that can write the input type
        $selectedMediaTypeFormatter = null;

        foreach ($this->mediaTypeFormatters as $mediaTypeFormatter) {
            if ($mediaTypeFormatter->canWriteType($type)) {
                $selectedMediaTypeFormatter = $mediaTypeFormatter;
                break;
            }
        }

        if ($selectedMediaTypeFormatter === null) {
            return new ContentNegotiationResult(null, null, null, $language);
        }

        $encoding = $this->encodingMatcher->getBestEncodingMatch(
            $selectedMediaTypeFormatter,
            $acceptCharsetHeaders,
            null
        );

        return new ContentNegotiationResult(
            $selectedMediaTypeFormatter,
            $selectedMediaTypeFormatter->getDefaultMediaType(),
            $encoding,
            $language
        );
    }
}
