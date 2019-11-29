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
 * Defines the alpha-numeric rule
 */
final class AlphaNumericRule implements IRule
{
    /**
     * @inheritdoc
     */
    public function getSlug(): string
    {
        return 'alphaNumeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value, array $allValues = []): bool
    {
        return ctype_alnum($value) && strpos($value, ' ') === false;
    }
}
