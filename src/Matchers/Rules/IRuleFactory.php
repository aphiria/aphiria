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

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the interface for rule factories to implement
 */
interface IRuleFactory
{
    /**
     * Creates a rule for the given slug
     *
     * @param string $slug The slug for the rule to create
     * @param array $params The list of params to pass into the factory
     * @return IRule An instance of the rule
     * @throws InvalidArgumentException Thrown if there's no factory registered for the slug
     * @throws RuntimeException Thrown if the factory does not return an instance of a rule
     */
    public function createRule(string $slug, array $params = []): IRule;

    /**
     * Registers a factory for a rule
     *
     * @param string $slug The slug to register a factory for
     * @param Closure $factory The factory that accepts an optional list of parameters and returns a rule instance
     */
    public function registerRuleFactory(string $slug, Closure $factory): void;
}
