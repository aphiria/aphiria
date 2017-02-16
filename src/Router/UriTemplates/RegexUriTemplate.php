<?php
namespace Opulence\Router\UriTemplates;

use Opulence\Router\UriTemplates\Rules\IRule;

/**
 * Defines a URI template that uses regexes for matching
 */
class RegexUriTemplate implements IUriTemplate
{
    /** @var string The regex to use to match URIs */
    private $uriRegex = '';
    /** @var array The mapping of route var names to their default values */
    private $defaultRouteVars = [];
    /** @var IRule[] The mapping of route var names to their rules */
    private $routeVarRules = [];

    /**
     * @param string $uriRegex The URI regex
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param IRule[] $routeVarRules The mapping of route var names to their rules
     */
    public function __construct(string $uriRegex, array $defaultRouteVars = [], array $routeVarRules = [])
    {
        $this->uriRegex = $uriRegex;
        $this->defaultRouteVars = $defaultRouteVars;
        $this->routeVarRules = $routeVarRules;
    }

    /**
     * @inheritdoc
     */
    public function buildTemplate(array $routeVars) : string
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function tryMatch(string $uri, array &$routeVars = []) : bool
    {
        $matches = [];
        
        if (preg_match($this->uriRegex, $uri, $matches) !== 1) {
            return false;
        }
        
        // Remove the subject
        array_shift($matches);
        
        // Set any missing route vars to their default values, if they have any
        foreach ($this->defaultRouteVars as $name => $defaultValue) {
            if (!isset($matches[$name])) {
                $matches[$name] = $defaultValue;
            }
        }
        
        // The matches will also contain numerical indices - we don't care about them
        foreach ($matches as $name => $value) {
            if (is_string($name)) {
                $routeVars[$name] = $value;
            }
        }
        
        return true;
    }
}
