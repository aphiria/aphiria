<?php
namespace Opulence\Router\UriTemplates\Compilers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\IUriTemplate;

/**
 * Defines the interface for URI template compilers to implement
 */
interface IUriTemplateCompiler
{
    /**
     * Compiles a raw URI template
     *
     * @param string $pathTemplate The raw path template
     * @param string|null $hostTemplate The raw host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return IUriTemplate The compiled URI template
     * @throws InvalidArgumentException Thrown if the template is invalid
     */
    public function compile(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false) : IUriTemplate;
}
