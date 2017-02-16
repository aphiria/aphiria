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
    /** @var IRule[][] The mapping of route var names to their rules */
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
        
        foreach ($routeVarRules as $name => $rules) {
            if (!is_array($rules)) {
                $rules = [$rules];
            }
            
            $this->routeVarRules[$name] = $rules;
        }
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
        $routeVars = [];
        $matches = [];
        
        if (preg_match($this->uriRegex, $uri, $matches) !== 1) {
            return false;
        }
        
        $this->populateRouteVars($routeVars, $matches);
        
        if (!$this->routeVarsPassRules($routeVars)) {
            // Reset the route vars
            $routeVars = [];
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Populates route vars from matches in the regex
     * 
     * @param array $routeVars The route vars to populate
     * @param array $matches The matches from the regex
     */
    private function populateRouteVars(array &$routeVars, array $matches) : void
    {
        $routeVars = [];
        
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
    }
    
    /**
     * Gets whether or not the route vars pass all the registered rules
     * 
     * @param array $routeVars The route vars to validate
     * @return bool True if all the route vars pass their rules, otherwise false
     */
    private function routeVarsPassRules(array $routeVars) : bool
    {
        foreach($this->routeVarRules as $name => $rules) {
            foreach ($rules as $rule) {
                if (isset($routeVars[$name]) && !$rule->passes($routeVars[$name])) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
