<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    /** @var IImmutableDictionary<string, string|null> The dictionary of parameter names to values */
    public readonly IImmutableDictionary $parameters;
    /** @var float The quality score of the header */
    private float $quality;

    /**
     * @param string $language The language value
     * @param IImmutableDictionary<string, string|null>|null $parameters The dictionary of parameters, or null if there are no parameters
     * @throws InvalidArgumentException Thrown if the quality score is not between 0 and 1
     */
    public function __construct(public readonly string $language, IImmutableDictionary $parameters = null)
    {
        /** @var IImmutableDictionary<string, string|null>|ImmutableHashTable<string, string|null> parameters */
        $this->parameters = $parameters ?? new ImmutableHashTable([]);
        $quality = null;
        $this->parameters->tryGet('q', $quality);
        // Specifically cast to float for type safety
        $this->quality = $quality === null ? 1.0 : (float)$quality;

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
