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
 * Defines metadata about a struct
 */
class StructMetadata extends TypeMetadata
{
    /** @var Closure The closure that can take in a value and return a serializable value */
    private $serialzier;

    /**
     * @param Closure $serializer The closure that can take in a value and return a serializable value
     */
    public function __construct(string $type, Closure $constructor, Closure $serializer)
    {
        parent::__construct($type, $constructor);

        $this->serialzier = $serializer;
    }

    /**
     * Gets the serializer
     *
     * @return Closure The closure that can take in a value and return a serializable value
     */
    public function getSerializer(): Closure
    {
        return $this->serialzier;
    }
}
