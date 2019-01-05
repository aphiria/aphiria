<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Headers;

use InvalidArgumentException;
use Opulence\Collections\IImmutableDictionary;
use Opulence\Collections\ImmutableHashTable;

/**
 * Defines the base class for media type header values to extend
 */
class MediaTypeHeaderValue
{
    /** @var string The value of the header */
    protected $mediaType;
    /** @var IImmutableDictionary The dictionary of parameter names to values */
    protected $parameters;
    /** @var string The type, eg "text" in "text/html" */
    private $type;
    /** @var string The sub-type, eg "html" in "text/html" */
    private $subType;
    /** @var string|null The charset if one was set, otherwise null */
    private $charset;

    /**
     * @param string $mediaType The media type
     * @param IImmutableDictionary|null $parameters The dictionary of parameter names to values, or null if no parameters
     * @throws InvalidArgumentException Thrown if the media type is not in the correct format
     */
    public function __construct(string $mediaType, IImmutableDictionary $parameters = null)
    {
        $this->mediaType = $mediaType;
        $this->parameters = $parameters ?? new ImmutableHashTable([]);
        $mediaTypeParts = explode('/', $mediaType);

        if (\count($mediaTypeParts) !== 2 || empty($mediaTypeParts[0]) || empty($mediaTypeParts[1])) {
            throw new InvalidArgumentException("Media type must be in format {type}/{sub-type}, received $mediaType");
        }

        $this->type = $mediaTypeParts[0];
        $this->subType = $mediaTypeParts[1];
        $this->parameters->tryGet('charset', $this->charset);
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
     * @return IImmutableDictionary The properties
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
     * Gets the media type
     *
     * @return string The type
     */
    public function getType(): string
    {
        return $this->type;
    }
}
