<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Defines the interface for rule factories to implement
 */
class IRuleFactory
{
    public function createRule(string $slug, array $params) : IRule;

    public function registerRuleFactory(string $slug, Closure $factory) : void;
}
