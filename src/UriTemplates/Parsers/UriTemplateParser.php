<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Parsers\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Parsers\Lexers\TokenTypes;
use InvalidArgumentException;

/**
 * Defines the URI template parser
 */
final class UriTemplateParser implements IUriTemplateParser
{
    /**
     * @inheritdoc
     */
    public function parse(TokenStream $tokens): AstNode
    {
        $ast = new AstNode(AstNodeTypes::ROOT, null);

        // Determine whether or not there's a host, and if so, parse it and the path
        $hostExists = false;
        $lookaheadBuffer = [];

        while (($token = $tokens->getCurrent()) !== null) {
            if ($token->type === TokenTypes::T_PUNCTUATION && $token->value === '/') {
                if ($hostExists) {
                    $hostNode = new AstNode(AstNodeTypes::HOST, null);
                    $this->parseTokens(new TokenStream($lookaheadBuffer), $hostNode);
                    $ast->addChild($hostNode);
                }

                $pathNode = new AstNode(AstNodeTypes::PATH, null);
                $this->parseTokens($tokens, $pathNode);
                $ast->addChild($pathNode);

                break;
            }

            // Anything besides an opening '[' prior to a '/' is considered the host
            if ($token->type !== TokenTypes::T_PUNCTUATION && $token->value !== '[') {
                $hostExists = true;
            }

            $lookaheadBuffer[] = $token;
            $tokens->next();
        }

        return $ast;
    }

    /**
     * Parses a punctuation token
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The abstract syntax tree to add nodes to
     * @param bool $parsingPath Whether or not we're parsing the path (otherwise we're parsing the host)
     * @throws InvalidArgumentException Thrown if an unexpected punctuation token was found
     */
    private function parsePunctuation(TokenStream $tokens, AstNode &$currNode, bool $parsingPath): void
    {
        if (($token = $tokens->getCurrent()) === null) {
            return;
        }

        switch ($token->value) {
            case '/':
                if (!$parsingPath) {
                    throw new InvalidArgumentException("Unexpected {$token->type} \"{$token->value}\" in host");
                }

                $currNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, $token->value));
                $tokens->next();
                break;
            case '.':
                // Periods in paths are to be treated as just text
                $nodeType = $parsingPath ? AstNodeTypes::TEXT : AstNodeTypes::SEGMENT_DELIMITER;
                $currNode->addChild(new AstNode($nodeType, $token->value));
                $tokens->next();
                break;
            case '[':
                $parentNodeType = $currNode->type;

                if (
                    $parentNodeType !== AstNodeTypes::HOST
                    && $parentNodeType !== AstNodeTypes::PATH
                    && $parentNodeType !== AstNodeTypes::OPTIONAL_ROUTE_PART
                ) {
                    throw new InvalidArgumentException("Unexpected {$token->type} \"{$token->value}\"");
                }

                $optionalRoutePartNode = new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, $token->value);
                $currNode->addChild($optionalRoutePartNode);
                $currNode = $optionalRoutePartNode;
                $tokens->next();

                if ($parsingPath) {
                    $tokens->expect(
                        TokenTypes::T_PUNCTUATION,
                        '/',
                        'Expected optional path part to start with \'/\', got %s'
                    );
                }

                break;
            case ']':
                if ($currNode->type !== AstNodeTypes::OPTIONAL_ROUTE_PART) {
                    // Just treat this as normal text
                    $currNode->addChild(new AstNode(AstNodeTypes::TEXT, $token->value));
                    $tokens->next();
                    break;
                }

                if (!$parsingPath) {
                    /**
                     * Optional parts in hosts must end with '.', eg [foo.[bar.]]example.com
                     * So, make sure that the previous non-optional route part ends with '.'
                     */
                    $isValid = false;

                    for ($i = \count($currNode->children) - 1;$i >= 0;$i--) {
                        $childNode = $currNode->children[$i];

                        if ($childNode->type !== AstNodeTypes::OPTIONAL_ROUTE_PART) {
                            if ($childNode->type === AstNodeTypes::SEGMENT_DELIMITER) {
                                $isValid = true;
                            }

                            break;
                        }
                    }

                    if (!$isValid) {
                        throw new InvalidArgumentException('Expected optional host part to end with \'.\'');
                    }
                }

                // End this optional route part
                $currNode = $currNode->parent;
                $tokens->next();
                break;
            default:
                // Since we handle punctuation inside of variables elsewhere, we'll just treat this as text
                $currNode->addChild(new AstNode(AstNodeTypes::TEXT, $token->value));
                $tokens->next();
                break;
        }
    }

    /**
     * Parses a text token
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The abstract syntax tree to add nodes to
     */
    private function parseText(TokenStream $tokens, AstNode $currNode): void
    {
        if (($token = $tokens->getCurrent()) === null) {
            return;
        }

        $currNode->addChild(new AstNode(AstNodeTypes::TEXT, $token->value));
        $tokens->next();
    }

    /**
     * Parses a stream of tokens and adds it to an abstract syntax tree
     *
     * @param TokenStream $tokens The tokens to parse
     * @param AstNode $ast The abstract syntax tree to add to
     */
    private function parseTokens(TokenStream $tokens, AstNode $ast): void
    {
        $parsingPath = $ast->type === AstNodeTypes::PATH;
        $currNode = $ast;

        while (($token = $tokens->getCurrent()) !== null) {
            switch ($token->type) {
                case TokenTypes::T_TEXT:
                    $this->parseText($tokens, $currNode);
                    break;
                case TokenTypes::T_NUMBER:
                    // Since we handle a number inside of variables elsewhere, we'll just treat this as text
                    $this->parseText($tokens, $currNode);
                    break;
                case TokenTypes::T_VARIABLE:
                    $this->parseVariable($tokens, $currNode);
                    break;
                case TokenTypes::T_PUNCTUATION:
                    $this->parsePunctuation($tokens, $currNode, $parsingPath);
                    break;
                case TokenTypes::T_QUOTED_STRING:
                    // Since we handle a quoted string inside of variables elsewhere, we'll just treat this as text
                    $this->parseText($tokens, $currNode);
                    break;
            }
        }
    }

    /**
     * Parses a stream of variable tokens
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The abstract syntax tree to add nodes to
     */
    private function parseVariable(TokenStream $tokens, AstNode &$currNode): void
    {
        if (($token = $tokens->getCurrent()) === null) {
            return;
        }

        $variableNode = new AstNode(AstNodeTypes::VARIABLE, $token->value);
        $currNode->addChild($variableNode);
        $currNode = $variableNode;
        $tokens->next();

        // Check for the beginning of a rule list
        if ($tokens->nextIfType(TokenTypes::T_PUNCTUATION, '(')) {
            // Parse all variable rules
            do {
                $this->parseVariableRule($tokens, $variableNode);
            } while ($tokens->nextIfType(TokenTypes::T_PUNCTUATION, ','));

            $tokens->expect(TokenTypes::T_PUNCTUATION, ')', 'Expected closing parenthesis after rules, got %s');
            $tokens->next();
        }

        if ($tokens->test(TokenTypes::T_VARIABLE)) {
            throw new InvalidArgumentException('Cannot have consecutive variables without a delimiter');
        }

        $currNode = $currNode->parent;
    }

    /**
     * Parses a single variable rule
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The variable node to add nodes to
     */
    private function parseVariableRule(TokenStream $tokens, AstNode $currNode): void
    {
        // Expect a rule name
        $tokens->expect(TokenTypes::T_TEXT, null, 'Expected rule name, got %s');

        if (($token = $tokens->getCurrent()) === null) {
            return;
        }

        $variableRuleNode = new AstNode(AstNodeTypes::VARIABLE_RULE, $token->value);
        $tokens->next();

        // Check for a parameter list for this rule
        if ($tokens->nextIfType(TokenTypes::T_PUNCTUATION, '(')) {
            $parameters = [];
            $currentToken = $tokens->getCurrent();

            while ($currentToken !== null && !$tokens->test(TokenTypes::T_PUNCTUATION, ')')) {
                if (!$tokens->test(TokenTypes::T_PUNCTUATION, ',')) {
                    $parameters[] = $currentToken->value;
                }

                $currentToken = $tokens->next();
            }

            $variableRuleNode->addChild(new AstNode(AstNodeTypes::VARIABLE_RULE_PARAMETERS, $parameters));
            $tokens->expect(
                TokenTypes::T_PUNCTUATION,
                ')',
                'Expected closing parenthesis after rule parameters, got %s'
            );
            $tokens->next();
        }

        $currNode->addChild($variableRuleNode);
    }
}
