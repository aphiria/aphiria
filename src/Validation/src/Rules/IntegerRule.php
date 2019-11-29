<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

/**
 * Defines the integer rule
 */
final class IntegerRule implements IRule
{
    /**
     * @inheritdoc
     */
    public function getSlug(): string
    {
        return 'integer';
    }

    /**
     * @inheritdoc
     */
    public function passes($value, array $allValues = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
