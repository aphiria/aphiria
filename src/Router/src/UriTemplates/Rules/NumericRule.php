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
 * Defines the numeric rule
 */
final class NumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'numeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return \is_numeric($value);
    }
}
