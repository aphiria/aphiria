<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Matchers\Rules\IRuleFactory;
use Aphiria\Routing\Matchers\Rules\RuleFactory;
use Aphiria\Routing\Matchers\Rules\RuleFactoryRegistrant;
use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeTypes;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\UriTemplateLexer;
use Aphiria\Routing\UriTemplates\Parsers\UriTemplateParser;
use InvalidArgumentException;

/**
 * Defines a compiler for a trie
 */
final class TrieCompiler implements ITrieCompiler
{
    /** @var IRuleFactory The factory that will create rules */
    private IRuleFactory $ruleFactory;
    /** @var IUriTemplateParser The URI template parser */
    private IUriTemplateParser $uriTemplateParser;
    /** @var IUriTemplateLexer The URI template lexer */
    private IUriTemplateLexer $uriTemplateLexer;

    /**
     * @param IRuleFactory|null $ruleFactory The factory that will create rules
     * @param IUriTemplateParser|null $uriTemplateParser The URI template parser
     * @param IUriTemplateLexer|null $uriTemplateLexer The URI template lexer
     */
    public function __construct(
        IRuleFactory $ruleFactory = null,
        IUriTemplateParser $uriTemplateParser = null,
        IUriTemplateLexer $uriTemplateLexer = null
    ) {
        if ($ruleFactory === null) {
            $this->ruleFactory = new RuleFactory();
            (new RuleFactoryRegistrant)->registerRuleFactories($this->ruleFactory);
        } else {
            $this->ruleFactory = $ruleFactory;
        }

        $this->uriTemplateParser = $uriTemplateParser ?? new UriTemplateParser();
        $this->uriTemplateLexer = $uriTemplateLexer ?? new UriTemplateLexer();
    }

    /**
     * @inheritdoc
     */
    public function compile(Route $route): TrieNode
    {
        $ast = $this->uriTemplateParser->parse($this->uriTemplateLexer->lex((string)$route->uriTemplate));
        $trie = new RootTrieNode();
        $hostTrie = null;

        foreach ($ast->children as $childAstNode) {
            switch ($childAstNode->type) {
                case AstNodeTypes::HOST:
                    $hostTrie = new RootTrieNode();
                    $this->compileNode(true, $hostTrie, $childAstNode, $route, null);
                    break;
                case AstNodeTypes::PATH:
                    $this->compileNode(false, $trie, $childAstNode, $route, $hostTrie);
                    break;
            }
        }

        return $trie;
    }

    /**
     * Creates a new node
     *
     * @param bool $isCompilingHostTrie Whether or not we're compiling a host trie
     * @param TrieNode $currTrieNode The current node
     * @param AstNode $ast The current AST node
     * @param Route $route The current route
     * @param TrieNode|null $hostTrie The host trie if one should be included, otherwise null
     */
    private function compileNode(
        bool $isCompilingHostTrie,
        TrieNode $currTrieNode,
        AstNode $ast,
        Route $route,
        ?TrieNode $hostTrie
    ): void {
        $astChildren = $ast->children;
        $numAstChildren = \count($astChildren);
        $isEndpoint = false;
        $segmentContainsVariable = false;
        $segmentBuffer = [];

        foreach ($isCompilingHostTrie ? \array_reverse($astChildren) : $astChildren as $i => $childAstNode) {
            /**
             * This isn't an endpoint if we're compiling a path trie which has a host trie
             * This is an endpoint if it's the last node or the last non-optional node
             */
            $isEndpoint = $isEndpoint
                || (
                    ($isCompilingHostTrie || $hostTrie === null)
                    && (
                        $i === $numAstChildren - 1
                        || $childAstNode->type === AstNodeTypes::OPTIONAL_ROUTE_PART
                        || ($astChildren[$i + 1]->type === AstNodeTypes::OPTIONAL_ROUTE_PART)
                    )
                );

            switch ($childAstNode->type) {
                case AstNodeTypes::SEGMENT_DELIMITER:
                    // Checking if this is an endpoint handles the case of a route at the root path
                    if ($isEndpoint || \count($segmentBuffer) > 0) {
                        $newTrieNode = self::createTrieNode(
                            $segmentBuffer,
                            $segmentContainsVariable,
                            $isEndpoint,
                            $route,
                            $hostTrie
                        );
                        $currTrieNode->addChild($newTrieNode);
                        $currTrieNode = $newTrieNode;
                    }

                    break;
                case AstNodeTypes::OPTIONAL_ROUTE_PART:
                    // Handles flushing 'foo' in the case of 'foo[/bar]'
                    if (\count($segmentBuffer) > 0) {
                        $newTrieNode = self::createTrieNode(
                            $segmentBuffer,
                            $segmentContainsVariable,
                            $isEndpoint,
                            $route,
                            $hostTrie
                        );
                        $currTrieNode->addChild($newTrieNode);
                        $currTrieNode = $newTrieNode;
                    }

                    $this->compileNode($isCompilingHostTrie, $currTrieNode, $childAstNode, $route, $hostTrie);
                    break;
                case AstNodeTypes::TEXT:
                    $segmentBuffer[] = $childAstNode->value;
                    break;
                case AstNodeTypes::VARIABLE:
                    $segmentContainsVariable = true;
                    $segmentBuffer[] = $this->compileVariableNode($childAstNode);
                    break;
                default:
                    throw new InvalidArgumentException("Unexpected node type {$childAstNode->type}");
            }
        }

        // Check if we need to flush the buffer
        if (\count($segmentBuffer) > 0) {
            $currTrieNode->addChild(
                self::createTrieNode($segmentBuffer, $segmentContainsVariable, $isEndpoint, $route, $hostTrie)
            );
        }
    }

    /**
     * Compiles a variable node
     *
     * @param AstNode $astNode The variable node to compile
     * @return RouteVariable The created route variable
     */
    private function compileVariableNode(AstNode $astNode): RouteVariable
    {
        $rules = [];

        foreach ($astNode->children as $childAstNode) {
            if ($childAstNode->type !== AstNodeTypes::VARIABLE_RULE) {
                throw new InvalidArgumentException("Unexpected node type {$childAstNode->type}");
            }

            $ruleParams = $childAstNode->hasChildren() ? $childAstNode->children[0]->value : [];
            $rules[] = $this->ruleFactory->createRule((string)$childAstNode->value, $ruleParams);
        }

        return new RouteVariable((string)$astNode->value, $rules);
    }

    /**
     * Creates a trie node
     *
     * @param string[]|RouteVariable[] $segmentBuffer The current buffer of parts (eg text or RouteVariables)
     * @param bool $segmentContainsVariable Whether or not the segment contains a variable
     * @param bool $isEndpoint Whether or not this node is an endpoint
     * @param Route $route The current route
     * @param TrieNode|null $hostTrie The host trie
     * @return TrieNode The created node
     */
    private static function createTrieNode(
        array &$segmentBuffer,
        bool &$segmentContainsVariable,
        bool $isEndpoint,
        Route $route,
        ?TrieNode $hostTrie
    ): TrieNode {
        $routes = $isEndpoint ? $route : [];

        if ($segmentContainsVariable) {
            $node = new VariableTrieNode($segmentBuffer, [], $routes, $hostTrie);
        } else {
            $node = new LiteralTrieNode(\implode('', $segmentBuffer), [], $routes, $hostTrie);
        }

        // Clear the buffer data
        $segmentBuffer = [];
        $segmentContainsVariable = false;

        return $node;
    }
}
