<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Headers;

/**
 * Defines the interface for header values with quality scores to implement
 */
interface IHeaderValueWithQualityScore
{
    /** @var float The quality score */
    public float $quality { get; }
}
