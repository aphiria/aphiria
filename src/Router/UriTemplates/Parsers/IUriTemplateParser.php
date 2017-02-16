<?php
namespace Opulence\Router\UriTemplates\Parsers;

use Opulence\Router\UriTemplates\IUriTemplate;

/**
 * Defines the interface for URI template parsers to implement
 */
interface IUriTemplateParser
{
    /**
     * Parses the raw URI templates
     * 
     * @param string $pathTemplate The raw path template to parse
     * @param string|null $hostTemplate The raw host template to parse
     * @param bool @isHttpsOnly Whether or not the URI is HTTPS-only
     * @return IUriTemplate The parsed URI template
     */
    public function parse(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false) : IUriTemplate;
}
