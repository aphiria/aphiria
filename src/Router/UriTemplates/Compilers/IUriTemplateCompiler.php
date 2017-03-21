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
     * @param string $rawUriTemplate The raw URI template to compile
     * @return IUriTemplate The compiled URI template
     * @throws InvalidArgumentException Thrown if template is invalid
     */
    public function compile(string $rawUriTemplate) : IUriTemplate;
}
