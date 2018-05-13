<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization\Encoding;

use Closure;

/**
 * Defines a contract
 */
abstract class Contract implements IContract
{
    /** @var string The type of value this contract represents */
    protected $type;
    /** @var Closure The factory that instantiates a value from a decoded value */
    protected $valueFactory;

    /**
     * @param string $type The type of value this contract represents
     * @param Closure $valueFactory The factory that instantiates a value from a decoded value
     */
    public function __construct(string $type, Closure $valueFactory)
    {
        $this->type = $type;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }
}
