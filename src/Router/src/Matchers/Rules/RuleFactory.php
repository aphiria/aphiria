<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Rules;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the rule factory
 */
final class RuleFactory implements IRuleFactory
{
    /** @var Closure[] The mapping of rule slugs to factories */
    private array $factories = [];

    /**
     * @inheritdoc
     */
    public function createRule(string $slug, array $params = []): IRule
    {
        if (!isset($this->factories[$slug])) {
            throw new InvalidArgumentException("No factory registered for rule \"$slug\"");
        }

        $rule = $this->factories[$slug](...$params);

        if (!$rule instanceof IRule) {
            throw new RuntimeException("Factory for rule \"$slug\" does not return an instance of IRule");
        }

        return $rule;
    }

    public function registerRuleFactory(string $slug, Closure $factory): void
    {
        $this->factories[$slug] = $factory;
    }
}
