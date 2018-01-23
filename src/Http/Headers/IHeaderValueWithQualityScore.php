<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Headers;

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
    public function getQuality() : float;
}
