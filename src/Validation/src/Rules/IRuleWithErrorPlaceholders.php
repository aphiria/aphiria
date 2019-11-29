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
 * Defines the interface for rules with error message placeholders
 */
interface IRuleWithErrorPlaceholders extends IRule
{
    /**
     * Gets the keyed array of error placeholders
     *
     * @return array The keyed array of placeholders => values
     */
    public function getErrorPlaceholders(): array;
}
