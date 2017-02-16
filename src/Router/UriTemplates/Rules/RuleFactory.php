<?php
namespace Opulence\Router\UriTemplates\Rules;

use Closure;

/**
 * Defines the rule factory
 */
class RuleFactory implements IRuleRegistry
{
    public function createRule(string $slug, array $params) : IRule
    {
        // Todo
    }

    public function registerRuleFactory(string $slug, Closure $factory) : void
    {
        // Todo
    }
}
