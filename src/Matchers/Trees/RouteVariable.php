<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Matchers\Trees;

use Aphiria\Routing\Matchers\Rules\IRule;

/**
 * Defines a route variable
 */
class RouteVariable
{
    /** @var string The name of the variable */
    public $name;
    /** @var IRule[] The list of rules that applies to this route variable */
    public $rules;

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