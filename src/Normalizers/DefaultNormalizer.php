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
 * Defines the default normalizer
 */
class DefaultNormalizer implements INormalizer
{
    /** @var NormalizerRegistry The registry of type normalizers */
    private $normalizerRegistry;

    /**
     * @param NormalizerRegistry $normalizerRegistry The registry of normalizers
     */
    public function __construct(NormalizerRegistry $normalizerRegistry)
    {
        $this->normalizerRegistry = $normalizerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        try {
            return $this->normalizerRegistry->getNormalizerForType($type)
                ->denormalize($value, $type);
        } catch (InvalidArgumentException $ex) {
            throw new NormalizationException('Failed to denormalize value', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function normalize($value, array $interceptors = [])
    {
        if ($value === null) {
            return null;
        }

        try {
            return $this->normalizerRegistry->getNormalizerForValue($value)
                ->normalize($value, $interceptors);
        } catch (InvalidArgumentException $ex) {
            throw new NormalizationException('Failed to normalize value', 0, $ex);
        }
    }
}
