<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Normalizers;

use InvalidArgumentException;

/**
 * Defines a scalar normalizer
 */
class ScalarNormalizer implements INormalizer
{
    /**
     * @inheritdoc
     */
    public function denormalize($value, string $type)
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                $denormalizedValue = (bool)$value;
                break;
            case 'float':
            case 'double':
                $denormalizedValue = (float)$value;
                break;
            case 'int':
            case 'integer':
                $denormalizedValue = (int)$value;
                break;
            default:
                throw new InvalidArgumentException("Type $type is an invalid scalar");
        }

        return $denormalizedValue;
    }

    /**
     * @inheritdoc
     */
    public function normalize($value, array $interceptors = [])
    {
        $normalizedValue = $value;

        foreach ($interceptors as $interceptor) {
            $normalizedValue = $interceptor->onPostNormalization($normalizedValue);
        }

        return $normalizedValue;
    }
}
