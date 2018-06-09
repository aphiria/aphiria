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
 * Defines the alphanumeric rule
 */
class AlphanumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'alphanumeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return ctype_alnum($value) && strpos($value, ' ') === false;
    }
}
