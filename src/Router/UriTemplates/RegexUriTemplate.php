<?php
namespace Opulence\Router\UriTemplates;

/**
 * Defines a URI template that uses regexes for matching
 */
class RegexUriTemplate implements IUriTemplate
{
    /** @var string The path regex to match with */
    private $pathRegex = '';
    /** @var string|null The host regex to match with */
    private $hostRegex = null;
    /** @var array The list of default values for route vars */
    private $defaultRouteVars = [];

    /**
     * @param string $pathRegex The path regex to match with
     * @param string|null $hostRegex The host regex to match with
     * @param array $defaultRouteVars The list of default values for route vars
     */
    public function __construct(string $pathRegex, string $hostRegex = null, array $defaultRouteVars = [])
    {
        $this->pathRegex = $pathRegex;
        $this->hostRegex = $hostRegex;
        $this->defaultRouteVars = $defaultRouteVars;
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
    public function tryMatch(string $value, array &$routeVars = []) : bool
    {
        // Todo
    }
}
