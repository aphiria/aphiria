<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

use Closure;

/**
 * Defines a struct encoder
 */
class StructEncoder extends Encoder
{
    /** @var Closure The factory that encodes the value this encoder encodes */
    protected $encodingFactory;

    /**
     * @inheritdoc
     * @param Closure $encodingFactory The factory that the value this encoder encodes
     */
    public function __construct(string $type, Closure $valueFactory, Closure $encodingFactory)
    {
        parent::__construct($type, $valueFactory);

        $this->encodingFactory = $encodingFactory;
    }

    /**
     * @inheritdoc
     */
    public function decode($value, array $encodingInterceptors = [])
    {
        foreach ($encodingInterceptors as $encodingInterceptor) {
            $value = $encodingInterceptor->onPreDecoding($value, $this->type);
        }

        return ($this->valueFactory)($value);
    }

    /**
     * @inheritdoc
     */
    public function encode($value, array $encodingInterceptors = [])
    {
        $encodedValue = ($this->encodingFactory)($value);

        foreach ($encodingInterceptors as $encodingInterceptor) {
            $encodedValue = $encodingInterceptor->onPostEncoding($encodedValue, $this->type);
        }

        return $encodedValue;
    }
}
