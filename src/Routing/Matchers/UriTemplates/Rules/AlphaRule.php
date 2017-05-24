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
 * Defines the alpha rule
 */
class AlphaRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'alpha';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return ctype_alpha($value) && strpos($value, ' ') === false;
    }
}
