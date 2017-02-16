<?php
namespace Opulence\Router\UriTemplates\Parsers;

use Opulence\Router\UriTemplates\IUriTemplate;

/**
 * Defines the regex URI template parser
 */
class RegexUriTemplateParser implements IUriTemplateParser
{
    /**
     * @inheritdoc
     */
    public function parse(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false) : IUriTemplate
    {
        // Todo
    }
    
    /**
     * Creates a URI regex
     * 
     * @param string $hostRegex The host regex
     * @param string $pathRegex The path regex
     * @param bool $isHttpsOnly Whether or not the URI is HTTPS-only
     * @return string The URI regex
     */
    private function createUriRegex(?string $hostRegex, string $pathRegex, bool $isHttpsOnly) : string
    {
        return '#^http' . ($isHttpsOnly ? 's' : '(?:s)?') . '://' . ($hostRegex ?? '[^/]+') . $pathRegex . '$#';
    }   
}
