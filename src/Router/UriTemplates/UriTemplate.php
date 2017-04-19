<?php
namespace Opulence\Router\UriTemplates;

/**
 * Defines a URI template
 */
class UriTemplate
{
    /** @var string The regex to match with */
    private $regex = '';
    /** 
     * The number of capturing groups in the regex, which is useful when it comes to chunked regex matching
     * 
     * @var int 
     */
    private $numCapturingGroups = 0;
    /** @var bool Whether or not this URI uses a host to match on */
    private $usesHost = false;
    /** @var bool Whether or not the URI is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var array The mapping of route var names to their default values */
    private $defaultRouteVars = [];
    /** @var IRule[][] The mapping of route var names to their rules */
    private $routeVarRules = [];
    
    /**
     * @param string $regex The regex to match with
     * @param int $numCapturingGroups The number of capturing groups within the regex
     * @param bool $usesHost Whether or not this URI uses a host to match on
     * @param bool $isHttpsOnly Whether or not the URI is HTTPS-only
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param array $routeVarRules The mapping of route var names to their rules
     */
    public function __construct(
            string $regex,
            int $numCapturingGroups,
            bool $usesHost,
            bool $isHttpsOnly = false,
            array $defaultRouteVars = [],
            array $routeVarRules = []
    ) {
        $this->regex = $regex;
        $this->numCapturingGroups = $numCapturingGroups;
        $this->usesHost = $usesHost;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->defaultRouteVars = $defaultRouteVars;
        
        foreach ($routeVarRules as $name => $rules) {
            if (!is_array($rules)) {
                $rules = [$rules];
            }
            
            $this->routeVarRules[$name] = $rules;
        }
    }
    
    /**
     * Gets the default values for the route vars in the URI template
     * 
     * @return array The mapping of route var names to their default values
     */
    public function getDefaultRouteVars() : array
    {
        return $this->defaultRouteVars;
    }
    
    /**
     * Gets the number of capturing groups in the regex
     * 
     * @return int The number of capturing groups (parenthesis pairs) in the regex
     */
    public function getNumCapturingGroups() : int
    {
        return $this->numCapturingGroups;
    }
    
    /**
     * Gets the regex
     * 
     * @return string The regex
     */
    public function getRegex() : string
    {
        return $this->regex;
    }
    
    /**
     * Gets the rules for the route vars in the URI template
     * 
     * @return array The mapping of route var names to their rules
     */
    public function getRouteVarRules() : array
    {
        return $this->routeVarRules;
    }
    
    /**
     * Gets whether or not the URI is HTTPS-only
     * 
     * @return bool True if the URI is HTTPS-only, otherwise false
     */
    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
    
    /**
     * Gets whether or not the URI uses a host to match on
     * 
     * @return bool True if the URI uses a host to match on, otherwise false
     */
    public function usesHost() : bool
    {
        return $this->usesHost;
    }
}
