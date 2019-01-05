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
 * Defines the Accept-Language header value
 */
class AcceptLanguageHeaderValue implements IHeaderValueWithQualityScore
{
    /** @var string The value of the header */
    protected $language;
    /** @var IImmutableDictionary The dictionary of parameter names to values */
    protected $parameters;
    /** @var float The quality score of the header */
    protected $quality;

    /**
     * @param string $language The language value
     * @param IImmutableDictionary $parameters The dictionary of parameters
     * @throws InvalidArgumentException Thrown if the quality score is not between 0 and 1
     */
    public function __construct(string $language, IImmutableDictionary $parameters = null)
    {
        $this->language = $language;
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
    public function getLanguage(): string
    {
        return $this->language;
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
