<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates\Rules;

/**
 * Defines the integer rule
 */
class IntegerRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'int';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
