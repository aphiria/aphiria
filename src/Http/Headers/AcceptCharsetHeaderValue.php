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
use Opulence\Collections\ImmutableHashTable;

/**
 * Defines the Accept-Charset header value
 */
class AcceptCharsetHeaderValue implements IHeaderValueWithQualityScore
{
    /** @var string The value of the header */
    protected $charset;
    /** @var IImmutableDictionary The dictionary of parameter names to values */
    protected $parameters;
    /** @var float The quality score of the header */
    protected $quality;

    /**
     * @param string $charset The charset value
     * @param IImmutableDictionary $parameters The dictionary of parameters
     * @throws InvalidArgumentException Thrown if the quality score is not between 0 and 1
     */
    public function __construct(string $charset, IImmutableDictionary $parameters = null)
    {
        $this->charset = $charset;
        $this->parameters = $parameters ?? new ImmutableHashTable([]);
        $this->quality = 1.0;
        $this->parameters->tryGet('q', $this->quality);

        if ($this->quality < 0 || $this->quality > 1) {
            throw new InvalidArgumentException('Quality score must be between 0 and 1, inclusive');
        }
    }

    /**
     * Gets the value of the header
     *
     * @return string The value of the header
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Gets the dictionary of parameters
     *
     * @return IImmutableDictionary The dictionary of parameters
     */
    public function getParameters(): IImmutableDictionary
    {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function getQuality(): float
    {
        return $this->quality;
    }
}
