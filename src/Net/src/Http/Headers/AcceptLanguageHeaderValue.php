<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Headers;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use InvalidArgumentException;

/**
 * Defines the Accept-Language header value
 */
final class AcceptLanguageHeaderValue implements IHeaderValueWithQualityScore
{
    /** @var IImmutableDictionary The dictionary of parameter names to values */
    private IImmutableDictionary $parameters;
    /** @var float The quality score of the header */
    private float $quality;

    /**
     * @param string $language The language value
     * @param IImmutableDictionary|null $parameters The dictionary of parameters, or null if there are no parameters
     * @throws InvalidArgumentException Thrown if the quality score is not between 0 and 1
     */
    public function __construct(private string $language, IImmutableDictionary $parameters = null)
    {
        $this->parameters = $parameters ?? new ImmutableHashTable([]);
        $quality = 1.0;
        $this->parameters->tryGet('q', $quality);
        // Specifically cast to float for type safety
        $this->quality = (float)$quality;

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
