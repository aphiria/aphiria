<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\Headers;

use InvalidArgumentException;
use Opulence\Collections\IImmutableDictionary;

/**
 * Defines the Accept media type header value
 */
final class AcceptMediaTypeHeaderValue extends MediaTypeHeaderValue implements IHeaderValueWithQualityScore
{
    /** @var float The quality score of the media type */
    private $quality;

    /**
     * @inheritdoc
     */
    public function __construct(string $mediaType, IImmutableDictionary $parameters = null)
    {
        parent::__construct($mediaType, $parameters);

        $this->quality = 1.0;
        $this->parameters->tryGet('q', $this->quality);

        if ($this->quality < 0 || $this->quality > 1) {
            throw new InvalidArgumentException('Quality score must be between 0 and 1, inclusive');
        }
    }

    /**
     * @inheritdoc
     */
    public function getQuality(): float
    {
        return $this->quality;
    }
}
