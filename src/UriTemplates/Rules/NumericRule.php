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
 * Defines the numeric rule
 */
class NumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'numeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return is_numeric($value);
    }
}
