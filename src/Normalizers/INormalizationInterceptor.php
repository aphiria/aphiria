<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Normalizers;

/**
 * Defines the interface for normalization interceptors to implement
 */
interface INormalizationInterceptor
{
    /**
     * Provides a hook for post-normalization a value
     *
     * @param mixed $normalizedValue The normalized value
     * @param string $type The type that is being normalized
     * @return mixed The modified normalized value
     */
    public function onPostNormalization($normalizedValue, string $type);
}
