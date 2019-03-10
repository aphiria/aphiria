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
 * Defines the interface for URI template rules to implement
 */
interface IRule
{
    /**
     * Gets whether or not the rule passes
     *
     * @param mixed $value The value to validate
     * @return bool True if the rule passes, otherwise false
     */
    public function passes($value): bool;
}
