<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Rules;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the rule factory
 */
class RuleFactory implements IRuleFactory
{
    /** @var Closure The mapping of rule slugs to factories */
    private $factories = [];

    /**
     * @inheritdoc
     */
    public function createRule(string $slug, array $params = []) : IRule
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

    public function registerRuleFactory(string $slug, Closure $factory) : void
    {
        $this->factories[$slug] = $factory;
    }
}
