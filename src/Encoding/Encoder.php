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
 * Defines an encoder
 */
abstract class Encoder implements IEncoder
{
    /** @var string The type of value this encoder encodes */
    protected $type;
    /** @var Closure The factory that instantiates a value from a decoded value */
    protected $constructor;

    /**
     * @param string $type The type of value this encoder encodes
     * @param Closure $constructor The factory that instantiates a value from a decoded value
     */
    public function __construct(string $type, Closure $constructor)
    {
        $this->type = $type;
        $this->constructor = $constructor;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }
}
