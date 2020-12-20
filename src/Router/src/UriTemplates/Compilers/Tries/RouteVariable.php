<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraint;

/**
 * Defines a route variable
 */
final class RouteVariable
{
    /**
     * @param string $name The name of the variable
     * @param IRouteVariableConstraint[] $constraints The list of constraints that applies to this route variable
     */
    public function __construct(public string $name, public array $constraints = [])
    {
    }
}
