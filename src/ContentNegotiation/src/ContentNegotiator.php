<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\HtmlMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\PlainTextMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\XmlMediaTypeFormatter;
use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\IRequest;
use InvalidArgumentException;

/**
 * Defines the default content negotiator
 */
final class ContentNegotiator implements IContentNegotiator
{
    /** @const The default media type if none is found (RFC 7231) */
    private const DEFAULT_REQUEST_MEDIA_TYPE = 'application/octet-stream';
    /** @var IMediaTypeFormatter[] The list of media type formatters */
    private array $mediaTypeFormatters;
    /** @var IMediaTypeFormatterMatcher The media type formatter matcher */
    private IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher;
    /** @var IEncodingMatcher The encoding matcher */
    private IEncodingMatcher $encodingMatcher;
    /** @var ILanguageMatcher The language matcher */
    private ILanguageMatcher $languageMatcher;
    /** @var RequestHeaderParser The header parser */
    private RequestHeaderParser $headerParser;

    /**
     * @param IMediaTypeFormatter[]|null $mediaTypeFormatters The list of media type formatters to use, or null if using the default formatters
     * @param IMediaTypeFormatterMatcher|null $mediaTypeFormatterMatcher The media type formatter matcher, or null if using the default one
     * @param IEncodingMatcher|null $encodingMatcher The encoding matcher, or null if using the default one
     * @param ILanguageMatcher|null $languageMatcher The language matcher, or null if using the default one
     * @param RequestHeaderParser|null $headerParser The header parser, or null if using the default one
     * @throws InvalidArgumentException Thrown if the list of media type formatters is empty
     */
    public function __construct(
        array $mediaTypeFormatters = null,
        IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher = null,
        IEncodingMatcher $encodingMatcher = null,
        ILanguageMatcher $languageMatcher = null,
        RequestHeaderParser $headerParser = null
    ) {
        if ($mediaTypeFormatters === null) {
            $mediaTypeFormatters = [
                new JsonMediaTypeFormatter(),
                new XmlMediaTypeFormatter(),
                new HtmlMediaTypeFormatter(),
                new PlainTextMediaTypeFormatter()
            ];
        }

        if (\count($mediaTypeFormatters) === 0) {
            throw new InvalidArgumentException('List of formatters cannot be empty');
        }

        $this->mediaTypeFormatters = $mediaTypeFormatters;
        $this->mediaTypeFormatterMatcher = $mediaTypeFormatterMatcher ?? new MediaTypeFormatterMatcher($this->mediaTypeFormatters);
        $this->encodingMatcher = $encodingMatcher ?? new AcceptCharsetEncodingMatcher();
        $this->languageMatcher = $languageMatcher ?? new AcceptLanguageMatcher(['en']);
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
                $acceptableMediaTypes = [
                    ...$acceptableMediaTypes,
                    ...$mediaTypeFormatter->getSupportedMediaTypes()
                ];
            }
        }

        return array_unique($acceptableMediaTypes);
    }

    /**
     * @inheritdoc
     */
    public function negotiateRequestContent(string $type, IRequest $request): ContentNegotiationResult
    {
        $requestHeaders = $request->getHeaders();
        $contentTypeHeader = $this->headerParser->parseContentTypeHeader($requestHeaders);
        $language = null;
        $requestHeaders->tryGetFirst('Content-Language', $language);
        /** @var string|null $language */

        if ($contentTypeHeader === null) {
            // We cannot negotiate the request content
            return new ContentNegotiationResult(null, self::DEFAULT_REQUEST_MEDIA_TYPE, null, $language);
        }

        $mediaTypeFormatterMatch = $this->mediaTypeFormatterMatcher->getBestRequestMediaTypeFormatterMatch(
            $type,
            $request
        );

        if ($mediaTypeFormatterMatch === null) {
            return new ContentNegotiationResult(null, null, null, $language);
        }

        $encoding = $this->encodingMatcher->getBestEncodingMatch(
            $mediaTypeFormatterMatch->getFormatter()->getSupportedEncodings(),
            $request,
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
    public function negotiateResponseContent(string $type, IRequest $request): ContentNegotiationResult
    {
        $language = $this->languageMatcher->getBestLanguageMatch($request);

        if (!$request->getHeaders()->containsKey('Accept')) {
            return $this->createDefaultResponseContentNegotiationResult($type, $language, $request);
        }

        $mediaTypeFormatterMatch = $this->mediaTypeFormatterMatcher->getBestResponseMediaTypeFormatterMatch(
            $type,
            $request
        );

        if ($mediaTypeFormatterMatch === null) {
            return new ContentNegotiationResult(null, null, null, $language);
        }

        $encoding = $this->encodingMatcher->getBestEncodingMatch(
            $mediaTypeFormatterMatch->getFormatter()->getSupportedEncodings(),
            $request,
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
     * @param IRequest $request The current request
     * @return ContentNegotiationResult The content negotiation result
     */
    private function createDefaultResponseContentNegotiationResult(
        string $type,
        ?string $language,
        IRequest $request
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
            $selectedMediaTypeFormatter->getSupportedEncodings(),
            $request
        );

        return new ContentNegotiationResult(
            $selectedMediaTypeFormatter,
            $selectedMediaTypeFormatter->getDefaultMediaType(),
            $encoding,
            $language
        );
    }
}
