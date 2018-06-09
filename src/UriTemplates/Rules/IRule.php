<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Rules;

/**
 * Defines the interface for URI template rules to implement
 */
interface IRule
{
    /**
     * Gets whether or not the rule passes
     *
     * @param mixed $value The value to validate
     * @return bool True if the rule passes, otherwise false
     */
    public function passes($value) : bool;
}
