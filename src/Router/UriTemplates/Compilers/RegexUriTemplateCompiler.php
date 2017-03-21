<?php
namespace Opulence\Router\UriTemplates\Compilers;

use Opulence\Router\UriTemplates\IUriTemplate;
use Opulence\Router\UriTemplates\Rules\IRuleFactory;

/**
 * Defines the URI template compiler that compiles to regex URI templates
 */
class RegexUriTemplateCompiler implements IUriTemplateCompiler
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
    public function compile(string $rawUriTemplate) : IUriTemplate
    {
        // Todo
    }
}
