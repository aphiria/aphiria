<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Aphiria\Net\Http\Headers\IHeaderValueWithQualityScore;
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;
use Aphiria\Net\Http\IRequest;
use InvalidArgumentException;

/**
 * Defines the media type formatter matcher
 */
final class MediaTypeFormatterMatcher implements IMediaTypeFormatterMatcher
{
    /** @const The type of formatter to match on for requests */
    private const FORMATTER_TYPE_INPUT = 'input';
    /** @const The type of formatter to match on for responses */
    private const FORMATTER_TYPE_OUTPUT = 'output';

    /**
     * @param list<IMediaTypeFormatter> $mediaTypeFormatters The list of supported media type formatters
     * @param RequestHeaderParser $headerParser The header parser to use to get request header values
     * @throws InvalidArgumentException Thrown if there are no media type formatters specified
     */
    public function __construct(
        private readonly array $mediaTypeFormatters,
        private readonly RequestHeaderParser $headerParser = new RequestHeaderParser()
    ) {
        if (\count($this->mediaTypeFormatters) === 0) {
            throw new InvalidArgumentException('List of formatters cannot be empty');
        }
    }

    /**
     * @inheritdoc
     */
    public function getBestRequestMediaTypeFormatterMatch(
        string $type,
        IRequest $request
    ): ?MediaTypeFormatterMatch {
        $contentTypeHeader = $this->headerParser->parseContentTypeHeader($request->getHeaders());

        return $this->getBestMediaTypeFormatterMatch(
            $type,
            $contentTypeHeader === null ? [] : [$contentTypeHeader],
            self::FORMATTER_TYPE_INPUT
        );
    }

    /**
     * @inheritdoc
     */
    public function getBestResponseMediaTypeFormatterMatch(
        string $type,
        IRequest $request
    ): ?MediaTypeFormatterMatch {
        return $this->getBestMediaTypeFormatterMatch(
            $type,
            $this->headerParser->parseAcceptHeader($request->getHeaders()),
            self::FORMATTER_TYPE_OUTPUT
        );
    }

    /**
     * Gets the best media type formatter match
     *
     * @param string $type The type that will be read/written by the formatter
     * @param list<MediaTypeHeaderValue> $mediaTypeHeaders The media type headers to match against
     * @param string $ioType Whether this is an input or an output media type formatter
     * @return MediaTypeFormatterMatch|null The media type formatter match if there was one, otherwise null
     */
    private function getBestMediaTypeFormatterMatch(
        string $type,
        array $mediaTypeHeaders,
        string $ioType
    ): ?MediaTypeFormatterMatch {
        // Rank the media type headers if they are rankable
        if (\count($mediaTypeHeaders) > 0 && $mediaTypeHeaders[0] instanceof AcceptMediaTypeHeaderValue) {
            $mediaTypeHeaders = $this->rankAcceptMediaTypeHeaders($mediaTypeHeaders);
        }

        foreach ($mediaTypeHeaders as $mediaTypeHeader) {
            [$mediaType, $mediaSubType] = \explode('/', $mediaTypeHeader->mediaType);

            foreach ($this->mediaTypeFormatters as $mediaTypeFormatter) {
                foreach ($mediaTypeFormatter->getSupportedMediaTypes() as $supportedMediaType) {
                    if ($ioType === self::FORMATTER_TYPE_INPUT && !$mediaTypeFormatter->canReadType($type)) {
                        continue;
                    }

                    if ($ioType === self::FORMATTER_TYPE_OUTPUT && !$mediaTypeFormatter->canWriteType($type)) {
                        continue;
                    }

                    [$supportedType, $supportedSubType] = \explode('/', $supportedMediaType);

                    // Checks if the type is a wildcard or a match and the sub-type is a wildcard or a match
                    if (
                        $mediaType === '*' ||
                        ($mediaSubType === '*' && $mediaType === $supportedType) ||
                        ($mediaType === $supportedType && $mediaSubType === $supportedSubType)
                    ) {
                        return new MediaTypeFormatterMatch($mediaTypeFormatter, $supportedMediaType, $mediaTypeHeader);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Compares two media types and returns which of them is "lower" than the other
     *
     * @param AcceptMediaTypeHeaderValue $a The first media type to compare
     * @param AcceptMediaTypeHeaderValue $b The second media type to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    private function compareAcceptMediaTypeHeaders(AcceptMediaTypeHeaderValue $a, AcceptMediaTypeHeaderValue $b): int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aType = $a->type;
        $bType = $b->type;
        $aSubType = $a->subType;
        $bSubType = $b->subType;

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
     * @param IHeaderValueWithQualityScore $header The value to check
     * @return bool True if we should keep the value, otherwise false
     */
    private function filterZeroScores(IHeaderValueWithQualityScore $header): bool
    {
        return $header->getQuality() > 0;
    }

    /**
     * Ranks the media type headers by quality, and then by specificity
     *
     * @param list<AcceptMediaTypeHeaderValue> $mediaTypeHeaders The list of media type headers to rank
     * @return list<AcceptMediaTypeHeaderValue> The ranked list of media type headers
     */
    private function rankAcceptMediaTypeHeaders(array $mediaTypeHeaders): array
    {
        \usort($mediaTypeHeaders, [$this, 'compareAcceptMediaTypeHeaders']);
        $rankedMediaTypeHeaders = \array_filter($mediaTypeHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter()
        return \array_values($rankedMediaTypeHeaders);
    }
}
