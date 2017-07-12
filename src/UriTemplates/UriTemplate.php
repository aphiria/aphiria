<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates;

/**
 * Defines a URI template
 */
class UriTemplate
{
    /** @var string The regex to match with */
    private $regex = '';
    /** @var bool Whether or not this URI is an absolute URI */
    private $isAbsoluteUri = false;
    /** @var array The list of route var names in the order they appear in the regex */
    private $routeVarNames = [];
    /** @var bool Whether or not the URI is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var array The mapping of route var names to their default values */
    private $defaultRouteVars = [];
    /** @var IRule[][] The mapping of route var names to their rules */
    private $routeVarRules = [];

    /**
     * @param string $regex The regex to match with
     * @param bool $isAbsoluteUri Whether or not this URI is an absolute URI
     * @param array $routeVarNames The list of route var names
     * @param bool $isHttpsOnly Whether or not the URI is HTTPS-only
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param array $routeVarRules The mapping of route var names to their rules
     */
    public function __construct(
        string $regex,
        bool $isAbsoluteUri,
        array $routeVarNames = [],
        bool $isHttpsOnly = false,
        array $defaultRouteVars = [],
        array $routeVarRules = []
    ) {
        $this->regex = $regex;
        $this->isAbsoluteUri = $isAbsoluteUri;
        $this->routeVarNames = $routeVarNames;
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
     * Gets the regex
     *
     * @return string The regex
     */
    public function getRegex() : string
    {
        return $this->regex;
    }

    /**
     * Gets the ordered list of route var names in the regex
     *
     * @return array The list of route var names
     */
    public function getRouteVarNames() : array
    {
        return $this->routeVarNames;
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
     * Gets whether or not the URI is an absolute URI
     *
     * @return bool True if the URI is absolute, otherwise false
     */
    public function isAbsoluteUri() : bool
    {
        return $this->isAbsoluteUri;
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
}
