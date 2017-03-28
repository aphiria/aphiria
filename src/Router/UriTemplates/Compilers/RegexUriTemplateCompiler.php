<?php
namespace Opulence\Router\UriTemplates\Compilers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Compilers\Parsers\IUriTemplateParser;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\IUriTemplateLexer;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\UriTemplateLexer;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;
use Opulence\Router\UriTemplates\Compilers\Parsers\UriTemplateParser;
use Opulence\Router\UriTemplates\IUriTemplate;
use Opulence\Router\UriTemplates\RegexUriTemplate;
use Opulence\Router\UriTemplates\Rules\IRuleFactory;

/**
 * Defines the URI template compiler that compiles to regex URI templates
 */
class RegexUriTemplateCompiler implements IUriTemplateCompiler
{
    /** @var string The regex delimiter to use */
    private const REGEX_DELIMITER = '#';
    /** @var IRuleFactory The factory for rules found in the routes */
    private $ruleFactory = null;
    /** @var IUriTemplateParser The URI template parser to use */
    private $parser = null;
    /** @var IUriTemplateLexer The URI template lexer to use */
    private $lexer = null;

    /**
     * @param IRuleFactory $ruleFactory The factory for rules found in the routes
     * @param IUriTemplateParser|null $parser The URI template parser to use
     * @param IUriTemplateLexer|null $lexer The URI template lexer to use
     */
    public function __construct(
        IRuleFactory $ruleFactory,
        IUriTemplateParser $parser = null,
        IUriTemplateLexer $lexer = null
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->parser = $parser ?? new UriTemplateParser();
        $this->lexer = $lexer ?? new UriTemplateLexer();
    }

    /**
     * @inheritdoc
     */
    public function compile(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false) : IUriTemplate
    {
        // Join the host and path templates into a URI template
        $rawUriTemplate = rtrim($hostTemplate ?? '', '/') . '/' . ltrim($pathTemplate, '/');
        $tokens = $this->lexer->lex($rawUriTemplate);
        $ast = $this->parser->parse($tokens);
        $defaultRouteVars = [];
        $routeVarRules = [];
        $protocolRegex = 'http' . ($isHttpsOnly ? 's' : '(?:s)?') . '://';
        $routeRegex = $this->compileNodes($ast->getRootNode(), $defaultRouteVars, $routeVarRules);
        
        // Add a default regex for an empty host template
        if ($hostTemplate === null) {
            $routeRegex = '[^/]+' . $routeRegex;
        }
        
        $regex = self::REGEX_DELIMITER . "^{$protocolRegex}{$routeRegex}$" . self::REGEX_DELIMITER;
        
        return new RegexUriTemplate($regex, $defaultRouteVars, $routeVarRules);
    }
    
    /**
     * Compiles an abstract syntax tree
     * 
     * @param Node $rootNode The root node to compile
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param IRule[] $routeVarRules The mapping of route var names to their rules
     * @return string The compiled regex
     * @throws InvalidArgumentException Thrown if the template is invalid
     */
    private function compileNodes(Node $rootNode, array &$defaultRouteVars, array &$routeVarRules) : string
    {
        $compiledRegex = '';
        
        foreach ($rootNode->getChildren() as $childNode) {
            switch ($childNode->getType()) {
                case NodeTypes::OPTIONAL_ROUTE_PART:
                    $compiledRegex .= $this->compileOptionalRoutePartNode($childNode, $defaultRouteVars, $routeVarRules);
                    break;
                case NodeTypes::TEXT:
                    $compiledRegex .= preg_quote($childNode->getValue(), self::REGEX_DELIMITER);
                    break;
                case NodeTypes::VARIABLE:
                    $compiledRegex .= $this->compileVariableNode($childNode, $defaultRouteVars, $routeVarRules);
                    break;
                default:
                    throw new InvalidArgumentException("Unexpected node type {$childNode->getType()}");
            }
        }
        
        return $compiledRegex;
    }
    
    /**
     * Compiles an optional route part node
     * 
     * @param Node $node The optional route part node to compile
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param IRule[] $routeVarRules The mapping of route var names to their rules
     * @return string The compiled regex
     */
    private function compileOptionalRoutePartNode(Node $node, array &$defaultRouteVars, array &$routeVarRules) : string
    {
        return "(?:{$this->compileNodes($node, $defaultRouteVars, $routeVarRules)})?";
    }
    
    /**
     * Compiles a variable node
     * 
     * @param Node $node The variable node to compile
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param IRule[] $routeVarRules The mapping of route var names to their rules
     * @return string The compiled regex
     * @throws InvalidArgumentException Thrown if an unexpected node is found
     */
    private function compileVariableNode(Node $node, array &$defaultRouteVars, array &$routeVarRules) : string
    {
        $variableName = $node->getValue();
        $regex = sprintf('(?P<%s>%s)', $variableName, '[^\/:]+');
        
        foreach ($node->getChildren() as $childNode) {
            switch ($childNode->getType()) {
                case NodeTypes::VARIABLE_DEFAULT_VALUE:
                    $defaultRouteVars[$variableName] = $childNode->getValue();
                    break;
                case NodeTypes::VARIABLE_RULE:
                    if (!isset($routeVarRules[$variableName])) {
                        $routeVarRules[$variableName] = [];
                    }

                    $ruleParams = $childNode->hasChildren() ? $childNode->getChildren()[0]->getValue() : [];
                    $routeVarRules[$variableName][] = $this->ruleFactory->createRule(
                        $childNode->getValue(),
                        $ruleParams
                    );
                    
                    break;
                default:
                    throw new InvalidArgumentException("Unexpected node type {$childNode->getType()}");
            }
        }
        
        return $regex;
    }
}
