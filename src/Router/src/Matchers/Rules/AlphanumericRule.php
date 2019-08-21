<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Rules;

/**
 * Defines the alphanumeric rule
 */
final class AlphanumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'alphanumeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return \ctype_alnum($value) && \strpos($value, ' ') === false;
    }
}
