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

/**
 * Defines a media type header value
 */
class MediaTypeHeaderValue
{
    /** @var string The type, eg "text" in "text/html" */
    private $type;
    /** @var string The sub-type, eg "html" in "text/html" */
    private $subType;
    /** @var float The quality score (0-1) */
    private $quality;

    /**
     * @param string $mediaType The media type
     * @param float $quality The quality score (0-1)
     * @throws InvalidArgumentException Thrown if the media type is incorrectly formatted or the quality is outside the allowed range
     */
    public function __construct(string $mediaType, ?float $quality = 1.0)
    {
        $mediaTypeParts = explode('/', $mediaType);

        if (count($mediaTypeParts) !== 2 || empty($mediaTypeParts[0]) || empty($mediaTypeParts[1])) {
            throw new InvalidArgumentException("Media type must be in format {type}/{sub-type}, received $mediaType");
        }

        $this->type = $mediaTypeParts[0];
        $this->subType = $mediaTypeParts[1];
        $this->quality = $quality ?? 1.0;

        if ($this->quality < 0 || $this->quality > 1) {
            throw new InvalidArgumentException('Quality score must be between 0 and 1, inclusive');
        }
    }

    /**
     * Gets the quality score
     *
     * @return float The quality score (0-1)
     */
    public function getQuality() : float
    {
        return $this->quality;
    }

    /**
     * Gets the full media type
     *
     * @return string The full media type
     */
    public function getFullMediaType() : string
    {
        return "{$this->type}/{$this->subType}";
    }

    /**
     * Gets the media sub-type
     *
     * @return string The sub-type
     */
    public function getSubType() : string
    {
        return $this->subType;
    }

    /**
     * Gets the media type
     *
     * @return string The type
     */
    public function getType() : string
    {
        return $this->type;
    }
}
