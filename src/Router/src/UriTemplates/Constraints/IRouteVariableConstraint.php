<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the interface for URI template constraints to implement
 */
interface IRouteVariableConstraint
{
    /**
     * Gets whether or not the constraint passes
     *
     * @param mixed $value The value to validate
     * @return bool True if the constraint passes, otherwise false
     */
    public function passes($value): bool;
}
