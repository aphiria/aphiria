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
 * Defines the default encoder
 */
class DefaultEncoder implements IEncoder
{
    /** @var EncoderRegistry The registry of type encoders */
    private $encoders;

    /**
     * @param EncoderRegistry $encoders The registry of encoders
     */
    public function __construct(EncoderRegistry $encoders)
    {
        $this->encoders = $encoders;
    }

    /**
     * @inheritdoc
     */
    public function decode($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        try {
            return $this->encoders->getEncoderForType($type)
                ->decode($value, $type);
        } catch (InvalidArgumentException $ex) {
            throw new EncodingException('Failed to decode value', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function encode($value, array $interceptors = [])
    {
        if ($value === null) {
            return null;
        }

        try {
            return $this->encoders->getEncoderForValue($value)
                ->encode($value, $interceptors);
        } catch (InvalidArgumentException $ex) {
            throw new EncodingException('Failed to encode value', 0, $ex);
        }
    }
}
