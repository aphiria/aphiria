<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\UriTemplates\Rules\IRule;

/**
 * Defines a route variable
 */
final class RouteVariable
{
    /** @var string The name of the variable */
    public string $name;
    /** @var IRule[] The list of rules that applies to this route variable */
    public array $rules;

    /**
     * @param string $name The name of the variable
     * @param IRule[] $rules The list of rules that applies to this route variable
     */
    public function __construct(string $name, array $rules = [])
    {
        $this->name = $name;
        $this->rules = $rules;
    }
}
