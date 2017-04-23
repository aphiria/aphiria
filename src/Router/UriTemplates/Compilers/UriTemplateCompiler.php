<?php
namespace Opulence\Router\UriTemplates\Compilers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Compilers\Parsers\IUriTemplateParser;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\IUriTemplateLexer;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\UriTemplateLexer;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;
use Opulence\Router\UriTemplates\Compilers\Parsers\UriTemplateParser;
use Opulence\Router\UriTemplates\Rules\IRuleFactory;
use Opulence\Router\UriTemplates\Rules\RuleFactory;
use Opulence\Router\UriTemplates\UriTemplate;

/**
 * Defines the URI template compiler that compiles to URI templates
 */
class UriTemplateCompiler implements IUriTemplateCompiler
{
    /** @var string The regex delimiter to use */
    private const REGEX_DELIMITER = '#';
    /** @var string The regex used to match route variables */
    private const ROUTE_VAR_REGEX = '([^\/:]+)';
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
        IRuleFactory $ruleFactory = null,
        IUriTemplateParser $parser = null,
        IUriTemplateLexer $lexer = null
    ) {
        $this->ruleFactory = $ruleFactory ?? new RuleFactory();
        $this->parser = $parser ?? new UriTemplateParser();
        $this->lexer = $lexer ?? new UriTemplateLexer();
    }

    /**
     * @inheritdoc
     */
    public function compile(?string $hostTemplate, string $pathTemplate, bool $isHttpsOnly = false) : UriTemplate
    {
        $routeVarNames = [];
        $defaultRouteVars = [];
        $routeVarRules = [];

        if ($hostTemplate === null || $hostTemplate === '') {
            $pathAst = $this->parser->parse($this->lexer->lex('/' . ltrim($pathTemplate, '/')));
            $pathRegex = $this->compileNodes(
                $pathAst->getRootNode(),
                $routeVarNames,
                $defaultRouteVars,
                $routeVarRules
            );
            $regex = "[^/]+$pathRegex";
        } else {
            // Join the host with the path
            $rawUriTemplate = rtrim($hostTemplate, '/') . '/' . ltrim($pathTemplate, '/');
            $uriAst = $this->parser->parse($this->lexer->lex($rawUriTemplate));
            $regex = $this->compileNodes(
                $uriAst->getRootNode(),
                $routeVarNames,
                $defaultRouteVars,
                $routeVarRules
            );
        }

        return new UriTemplate(
            "^$regex$",
            !empty($hostTemplate),
            $routeVarNames,
            $isHttpsOnly,
            $defaultRouteVars,
            $routeVarRules
        );
    }

    /**
     * Compiles an abstract syntax tree
     *
     * @param Node $rootNode The root node to compile
     * @param array $routeVarNames The list of route var names
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param IRule[] $routeVarRules The mapping of route var names to their rules
     * @return string The compiled regex
     * @throws InvalidArgumentException Thrown if the template is invalid
     */
    private function compileNodes(
        Node $rootNode,
        array &$routeVarNames,
        array &$defaultRouteVars,
        array &$routeVarRules
    ) : string {
        $compiledRegex = '';

        foreach ($rootNode->getChildren() as $childNode) {
            switch ($childNode->getType()) {
                case NodeTypes::OPTIONAL_ROUTE_PART:
                    $compiledRegex .= $this->compileOptionalRoutePartNode(
                        $childNode,
                        $routeVarNames,
                        $defaultRouteVars,
                        $routeVarRules
                    );
                    break;
                case NodeTypes::TEXT:
                    $compiledRegex .= preg_quote($childNode->getValue(), self::REGEX_DELIMITER);
                    break;
                case NodeTypes::VARIABLE:
                    $compiledRegex .= $this->compileVariableNode(
                        $childNode,
                        $routeVarNames,
                        $defaultRouteVars,
                        $routeVarRules
                    );
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
     * @param array $routeVarNames The list of route var names
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param IRule[] $routeVarRules The mapping of route var names to their rules
     * @return string The compiled regex
     */
    private function compileOptionalRoutePartNode(
        Node $node,
        array &$routeVarRegexPositions,
        array &$defaultRouteVars,
        array &$routeVarRules
    ) : string {
        return "(?:{$this->compileNodes($node, $routeVarRegexPositions, $defaultRouteVars, $routeVarRules)})?";
    }

    /**
     * Compiles a variable node
     *
     * @param Node $node The variable node to compile
     * @param array $routeVarNames The list of route var names
     * @param array $defaultRouteVars The mapping of route var names to their default values
     * @param IRule[] $routeVarRules The mapping of route var names to their rules
     * @return string The compiled regex
     * @throws InvalidArgumentException Thrown if an unexpected node is found
     */
    private function compileVariableNode(
        Node $node,
        array &$routeVarNames,
        array &$defaultRouteVars,
        array &$routeVarRules
    ) : string {
        $variableName = $node->getValue();
        $routeVarNames[] = $variableName;

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

        return self::ROUTE_VAR_REGEX;
    }
}
