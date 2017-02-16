<?php
namespace Opulence\Router\UriTemplates\Parsers;

use Opulence\Router\UriTemplates\IUriTemplate;
use Opulence\Router\UriTemplates\Rules\IRuleFactory;

/**
 * Defines the regex URI template parser
 */
class RegexUriTemplateParser implements IUriTemplateParser
{
    /** @var IRuleFactory The factory for rules found in the routes */
    private $ruleFactory = null;

    /**
     * @param IRuleFactory $ruleFactory The factory for rules found in the routes
     */
    public function __construct(IRuleFactory $ruleFactory)
    {
        $this->ruleFactory = $ruleFactory;
    }

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
