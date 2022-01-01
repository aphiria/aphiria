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

/**
 * Defines the interface for header values with quality scores to implement
 */
interface IHeaderValueWithQualityScore
{
    /**
     * Gets the quality score
     *
     * @return float The quality score (0-1)
     */
    public function getQuality(): float;
}
