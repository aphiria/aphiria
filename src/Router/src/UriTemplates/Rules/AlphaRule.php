<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Rules;

/**
 * Defines the alpha rule
 */
final class AlphaRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'alpha';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return \ctype_alpha($value) && \strpos($value, ' ') === false;
    }
}
