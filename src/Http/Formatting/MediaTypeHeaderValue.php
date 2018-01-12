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
     * @param string $type The type, eg "text" in "text/html"
     * @param string $subType The sub-type, eg "html" in "text/html"
     * @param float $quality The quality score (0-1)
     * @throws InvalidArgumentException Thrown if the quality is outside the allowed range
     */
    public function __construct(string $type, string $subType, ?float $quality = 1.0)
    {
        $this->type = $type;
        $this->subType = $subType;
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
