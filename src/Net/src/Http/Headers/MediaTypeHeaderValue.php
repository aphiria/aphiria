<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Headers;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use InvalidArgumentException;

/**
 * Defines the base class for media type header values to extend
 */
class MediaTypeHeaderValue
{
    /** @var IImmutableDictionary<string, string|null> The dictionary of parameter names to values */
    protected IImmutableDictionary $parameters;
    /** @var string The type, eg "text" in "text/html" */
    private string $type;
    /** @var string The sub-type, eg "html" in "text/html" */
    private string $subType;
    /**
     * The media type suffix, eg "json" in "application/foo+json
     * @link https://tools.ietf.org/html/rfc6839
     * @var string|null
     */
    private ?string $suffix = null;
    /** @var string|null The charset if one was set, otherwise null */
    private ?string $charset = null;

    /**
     * @param string $mediaType The media type
     * @param IImmutableDictionary<string, string|null>|null $parameters The dictionary of parameter names to values, or null if no parameters
     * @throws InvalidArgumentException Thrown if the media type is not in the correct format
     */
    public function __construct(protected string $mediaType, IImmutableDictionary $parameters = null)
    {
        /** @var IImmutableDictionary<string, string|null>|ImmutableHashTable<string, string|null> parameters */
        $this->parameters = $parameters ?? new ImmutableHashTable([]);
        $mediaTypeParts = \explode('/', $mediaType);

        if (\count($mediaTypeParts) !== 2 || empty($mediaTypeParts[0]) || empty($mediaTypeParts[1])) {
            throw new InvalidArgumentException("Media type must be in format {type}/{sub-type}, received $mediaType");
        }

        $this->type = $mediaTypeParts[0];
        $this->subType = $mediaTypeParts[1];

        if (\str_contains($this->mediaType, '+') && ($plusSignPos = \strpos($this->mediaType, '+')) !== false) {
            $this->suffix = \substr($this->mediaType, $plusSignPos + 1);
        }

        $charset = null;

        if ($this->parameters->tryGet('charset', $charset)) {
            $this->charset = (string)$charset;
        }
    }

    /**
     * Gets the charset
     *
     * @return string|null The charset if one was set, otherwise null
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * Gets the value of the header
     *
     * @return string The value of the header
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    /**
     * Gets the dictionary of parameter names to values
     *
     * @return IImmutableDictionary<string, string|null> The properties
     */
    public function getParameters(): IImmutableDictionary
    {
        return $this->parameters;
    }

    /**
     * Gets the media sub-type
     *
     * @return string The sub-type
     */
    public function getSubType(): string
    {
        return $this->subType;
    }

    /**
     * Gets the media sub-type without the suffix
     *
     * @return string The sub-type without the suffix
     */
    public function getSubTypeWithoutSuffix(): string
    {
        if ($this->suffix === null) {
            return $this->subType;
        }

        return \str_replace("+{$this->suffix}", '', $this->subType);
    }

    /**
     * Gets the media type suffix
     *
     * @return string|null The media type suffix if it existed, otherwise null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * Gets the media type
     *
     * @return string The type
     */
    public function getType(): string
    {
        return $this->type;
    }
}
