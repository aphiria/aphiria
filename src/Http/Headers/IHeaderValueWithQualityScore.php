<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

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
