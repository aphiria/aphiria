<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Rules;

/**
 * Defines the integer rule
 */
final class IntegerRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'int';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return \filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
