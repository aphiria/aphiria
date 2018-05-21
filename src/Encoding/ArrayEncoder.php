<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

use InvalidArgumentException;

/**
 * Defines the array encoder
 */
class ArrayEncoder implements IEncoder
{
    /** @var IEncoder The parent encoder */
    private $parentEncoder;

    /**
     * @param IEncoder $parentEncoder The parent encoder
     */
    public function __construct(IEncoder $parentEncoder)
    {
        $this->parentEncoder = $parentEncoder;
    }

    /**
     * @inheritdoc
     */
    public function decode($values, string $type)
    {
        if (substr($type, -2, 2) !== '[]') {
            throw new InvalidArgumentException('Type must end in "[]"');
        }

        if (!\is_array($values)) {
            throw new InvalidArgumentException('Value must be an array');
        }

        $actualType = substr($type, 0, -2);
        $decodedValues = [];

        foreach ($values as $value) {
            $decodedValues[] = $this->parentEncoder->decode($value, $actualType);
        }

        return $decodedValues;
    }

    /**
     * @inheritdoc
     */
    public function encode($values)
    {
        if (!\is_array($values)) {
            throw new InvalidArgumentException('Value must be an array');
        }

        if (\count($values) === 0) {
            return [];
        }

        $encodedValues = [];

        foreach ($values as $value) {
            $encodedValues[] = $this->parentEncoder->encode($value);
        }

        return $encodedValues;
    }
}
