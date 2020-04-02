<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Encoding;

use InvalidArgumentException;

/**
 * Defines the array encoder
 */
final class ArrayEncoder implements IEncoder
{
    /** @var EncoderRegistry The encoder registry */
    private EncoderRegistry $encoders;

    /**
     * @param EncoderRegistry $encoders The encoder registry
     */
    public function __construct(EncoderRegistry $encoders)
    {
        $this->encoders = $encoders;
    }

    /**
     * @inheritdoc
     */
    public function decode($values, string $type, EncodingContext $context)
    {
        if (substr($type, -2, 2) !== '[]') {
            throw new InvalidArgumentException('Type must end in "[]"');
        }

        if (!\is_array($values)) {
            throw new InvalidArgumentException('Value must be an array');
        }

        $actualType = substr($type, 0, -2);
        $encoder = $this->encoders->getEncoderForType($actualType);
        $decodedValues = [];

        foreach ($values as $value) {
            $decodedValues[] = $encoder->decode($value, $actualType, $context);
        }

        return $decodedValues;
    }

    /**
     * @inheritdoc
     */
    public function encode($values, EncodingContext $context)
    {
        if (!\is_array($values)) {
            throw new InvalidArgumentException('Value must be an array');
        }

        if (\count($values) === 0) {
            return [];
        }

        $encodedValues = [];

        foreach ($values as $value) {
            $encodedValues[] = $this->encoders->getEncoderForValue($value)
                ->encode($value, $context);
        }

        return $encodedValues;
    }
}
