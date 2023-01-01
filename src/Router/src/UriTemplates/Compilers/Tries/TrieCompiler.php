<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\Constraints\RouteVariableConstraintFactory;
use Aphiria\Routing\UriTemplates\Constraints\RouteVariableConstraintFactoryRegistrant;
use Aphiria\Routing\UriTemplates\InvalidUriTemplateException;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\LexingException;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use Aphiria\Routing\UriTemplates\Lexers\UriTemplateLexer;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeType;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\Parsers\UriTemplateParser;

/**
 * Defines a compiler for a trie
 */
final class TrieCompiler implements ITrieCompiler
{
    /** @var RouteVariableConstraintFactory The factory that will create constraints */
    private readonly RouteVariableConstraintFactory $constraintFactory;

    /**
     * @param RouteVariableConstraintFactory|null $constraintFactory The factory that will create constraints
     * @param IUriTemplateParser $uriTemplateParser The URI template parser
     * @param IUriTemplateLexer $uriTemplateLexer The URI template lexer
     */
    public function __construct(
        RouteVariableConstraintFactory $constraintFactory = null,
        private readonly IUriTemplateParser $uriTemplateParser = new UriTemplateParser(),
        private readonly IUriTemplateLexer $uriTemplateLexer = new UriTemplateLexer()
    ) {
        if ($constraintFactory === null) {
            $this->constraintFactory = new RouteVariableConstraintFactory();
            (new RouteVariableConstraintFactoryRegistrant())->registerConstraintFactories($this->constraintFactory);
        } else {
            $this->constraintFactory = $constraintFactory;
        }
    }

    /**
     * @inheritdoc
     */
    public function compile(Route $route): TrieNode
    {
        try {
            $ast = $this->uriTemplateParser->parse($this->uriTemplateLexer->lex((string)$route->uriTemplate));
            $trie = new RootTrieNode();
            $hostTrie = null;

            foreach ($ast->children as $childAstNode) {
                switch ($childAstNode->type) {
                    case AstNodeType::Host:
                        $hostTrie = new RootTrieNode();
                        $this->compileNode(true, $hostTrie, $childAstNode, $route, null);
                        break;
                    case AstNodeType::Path:
                        $this->compileNode(false, $trie, $childAstNode, $route, $hostTrie);
                        break;
                }
            }

            return $trie;
        } catch (UnexpectedTokenException | LexingException $ex) {
            throw new InvalidUriTemplateException('URI template could not be compiled', 0, $ex);
        }
    }

    /**
     * Creates a trie node
     *
     * @param list<string|RouteVariable> $segmentBuffer The current buffer of parts (eg text or RouteVariables)
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
            /** @var list<string> $segmentBuffer */
            $node = new LiteralTrieNode(\implode('', $segmentBuffer), [], $routes, $hostTrie);
        }

        // Clear the buffer data
        $segmentBuffer = [];
        $segmentContainsVariable = false;

        return $node;
    }

    /**
     * Creates a new node
     *
     * @param bool $isCompilingHostTrie Whether or not we're compiling a host trie
     * @param TrieNode $currTrieNode The current node
     * @param AstNode $ast The current AST node
     * @param Route $route The current route
     * @param TrieNode|null $hostTrie The host trie if one should be included, otherwise null
     * @throws InvalidUriTemplateException Thrown if there is an unexpected AST node
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
        /** @var list<string|RouteVariable> $segmentBuffer */
        $segmentBuffer = [];

        foreach ($isCompilingHostTrie ? \array_reverse($astChildren) : $astChildren as $i => $childAstNode) {
            /**
             * This isn't an endpoint if we're compiling a path trie which has a host trie
             * This is an endpoint if it's the last node or the last non-optional node
             * @psalm-suppress RedundantCast We do not want to rely on PHPDoc alone
             */
            $isEndpoint = $isEndpoint
                || (
                    ($isCompilingHostTrie || $hostTrie === null)
                    && (
                        $i === $numAstChildren - 1
                        || $childAstNode->type === AstNodeType::OptionalRoutePart
                        || ($astChildren[(int)$i + 1]->type === AstNodeType::OptionalRoutePart)
                    )
                );

            switch ($childAstNode->type) {
                case AstNodeType::SegmentDelimiter:
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
                case AstNodeType::OptionalRoutePart:
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
                case AstNodeType::Text:
                    $segmentBuffer[] = (string)$childAstNode->value;
                    break;
                case AstNodeType::Variable:
                    $segmentContainsVariable = true;
                    $segmentBuffer[] = $this->compileVariableNode($childAstNode);
                    break;
                default:
                    throw new InvalidUriTemplateException("Unexpected node type {$childAstNode->type->name}");
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
     * @throws InvalidUriTemplateException Thrown if there is an unexpected AST node
     */
    private function compileVariableNode(AstNode $astNode): RouteVariable
    {
        $constraints = [];

        foreach ($astNode->children as $childAstNode) {
            if ($childAstNode->type !== AstNodeType::VariableConstraint) {
                throw new InvalidUriTemplateException("Unexpected node type {$childAstNode->type->name}");
            }

            /** @var list<mixed> $constraintParams */
            $constraintParams = $childAstNode->hasChildren() ? $childAstNode->children[0]->value : [];
            $constraints[] = $this->constraintFactory->createConstraint(
                (string)$childAstNode->value,
                (array)$constraintParams
            );
        }

        return new RouteVariable((string)$astNode->value, $constraints);
    }
}
