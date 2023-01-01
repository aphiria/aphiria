<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\TokenType;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;

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
        $ast = new AstNode(AstNodeType::Root, null);

        // Determine whether or not there's a host, and if so, parse it and the path
        $hostExists = false;
        $lookaheadBuffer = [];

        while (($token = $tokens->getCurrent()) !== null) {
            if ($token->type === TokenType::Punctuation && $token->value === '/') {
                if ($hostExists) {
                    $hostNode = new AstNode(AstNodeType::Host, null);
                    $this->parseTokens(new TokenStream($lookaheadBuffer), $hostNode);
                    $ast->addChild($hostNode);
                }

                $pathNode = new AstNode(AstNodeType::Path, null);
                $this->parseTokens($tokens, $pathNode);
                $ast->addChild($pathNode);
                break;
            }

            // Anything besides an opening '[' prior to a '/' is considered the host
            if ($token->type !== TokenType::Punctuation && $token->value !== '[') {
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
     * @param Token $currToken The current token
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The abstract syntax tree to add nodes to
     * @param bool $parsingPath Whether or not we're parsing the path (otherwise we're parsing the host)
     * @throws UnexpectedTokenException Thrown if an unexpected punctuation token was found
     * @psalm-suppress ReferenceConstraintViolation The current node will never be the root node, which means its parent will never be null
     */
    private function parsePunctuation(Token $currToken, TokenStream $tokens, AstNode &$currNode, bool $parsingPath): void
    {
        switch ($currToken->value) {
            case '/':
                // We don't have to worry about parsing a slash in the host because slashes are not added to the host lookahead buffer
                $currNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, $currToken->value));
                $tokens->next();
                break;
            case '.':
                // Periods in paths are to be treated as just text
                $nodeType = $parsingPath ? AstNodeType::Text : AstNodeType::SegmentDelimiter;
                $currNode->addChild(new AstNode($nodeType, $currToken->value));
                $tokens->next();
                break;
            case '[':
                /**
                 * We do not allow optional parts unless the parent node is a host, path, or another optional part.
                 * We are guarded against this happening, though, by checks when parsing variables (the only place you
                 * could accidentally insert an optional part into an invalid parent node).  So, we don't bother
                 * checking the parent node here.
                 */
                $optionalRoutePartNode = new AstNode(AstNodeType::OptionalRoutePart, $currToken->value);
                $currNode->addChild($optionalRoutePartNode);
                $currNode = $optionalRoutePartNode;
                $tokens->next();

                if ($parsingPath) {
                    $tokens->expect(
                        TokenType::Punctuation,
                        '/',
                        'Expected optional path part to start with \'/\', got %s'
                    );
                }

                break;
            case ']':
                if ($currNode->type !== AstNodeType::OptionalRoutePart) {
                    // Just treat this as normal text
                    $currNode->addChild(new AstNode(AstNodeType::Text, $currToken->value));
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

                        if ($childNode->type !== AstNodeType::OptionalRoutePart) {
                            if ($childNode->type === AstNodeType::SegmentDelimiter) {
                                $isValid = true;
                            }

                            break;
                        }
                    }

                    if (!$isValid) {
                        throw new UnexpectedTokenException('Expected optional host part to end with \'.\'');
                    }
                }

                // End this optional route part
                $currNode = $currNode->parent;
                $tokens->next();
                break;
            default:
                // Since we handle punctuation inside of variables elsewhere, we'll just treat this as text
                $currNode->addChild(new AstNode(AstNodeType::Text, $currToken->value));
                $tokens->next();
                break;
        }
    }

    /**
     * Parses a text token
     *
     * @param Token $currToken The current token
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The abstract syntax tree to add nodes to
     */
    private function parseText(Token $currToken, TokenStream $tokens, AstNode $currNode): void
    {
        $currNode->addChild(new AstNode(AstNodeType::Text, $currToken->value));
        $tokens->next();
    }

    /**
     * Parses a stream of tokens and adds it to an abstract syntax tree
     *
     * @param TokenStream $tokens The tokens to parse
     * @param AstNode $ast The abstract syntax tree to add to
     * @throws UnexpectedTokenException Thrown if there was an unexpected token
     */
    private function parseTokens(TokenStream $tokens, AstNode $ast): void
    {
        $parsingPath = $ast->type === AstNodeType::Path;
        $currNode = $ast;

        while (($token = $tokens->getCurrent()) !== null) {
            switch ($token->type) {
                case TokenType::Text:
                    $this->parseText($token, $tokens, $currNode);
                    break;
                case TokenType::Number:
                    // Since we handle a number inside of variables elsewhere, we'll just treat this as text
                    $this->parseText($token, $tokens, $currNode);
                    break;
                case TokenType::Variable:
                    $this->parseVariable($token, $tokens, $currNode);
                    break;
                case TokenType::Punctuation:
                    $this->parsePunctuation($token, $tokens, $currNode, $parsingPath);
                    break;
                case TokenType::QuotedString:
                    // Since we handle a quoted string inside of variables elsewhere, we'll just treat this as text
                    $this->parseText($token, $tokens, $currNode);
                    break;
            }
        }
    }

    /**
     * Parses a stream of variable tokens
     *
     * @param Token $currToken The current token
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The abstract syntax tree to add nodes to
     * @throws UnexpectedTokenException Thrown if there was an unexpected token
     * @psalm-suppress ReferenceConstraintViolation The current node will never be the root node, which means its parent will never be null
     */
    private function parseVariable(Token $currToken, TokenStream $tokens, AstNode &$currNode): void
    {
        $variableNode = new AstNode(AstNodeType::Variable, $currToken->value);
        $currNode->addChild($variableNode);
        $currNode = $variableNode;
        $tokens->next();

        // Check for the beginning of a constraint list
        if ($tokens->nextIfType(TokenType::Punctuation, '(')) {
            // Parse all variable constraints
            do {
                /** @psalm-suppress PossiblyNullArgument Above, we've verified that the current token is punctuation */
                $this->parseVariableConstraint($tokens->getCurrent(), $tokens, $variableNode);
            } while ($tokens->nextIfType(TokenType::Punctuation, ','));

            $tokens->expect(TokenType::Punctuation, ')', 'Expected closing parenthesis after constraints, got %s');
            $tokens->next();
        }

        if ($tokens->test(TokenType::Variable)) {
            throw new UnexpectedTokenException('Cannot have consecutive variables without a delimiter');
        }

        $currNode = $currNode->parent;
    }

    /**
     * Parses a single variable constraint
     *
     * @param Token $currToken The current token
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AstNode $currNode The variable node to add nodes to
     * @throws UnexpectedTokenException Thrown if there was an unexpected token
     */
    private function parseVariableConstraint(Token $currToken, TokenStream $tokens, AstNode $currNode): void
    {
        // Expect a constraint name
        $tokens->expect(TokenType::Text, null, 'Expected constraint name, got %s');
        $variableConstraintNode = new AstNode(AstNodeType::VariableConstraint, $currToken->value);
        $tokens->next();

        // Check for a parameter list for this constraint
        if ($tokens->nextIfType(TokenType::Punctuation, '(')) {
            $parameters = [];
            $currentToken = $tokens->getCurrent();

            while ($currentToken !== null && !$tokens->test(TokenType::Punctuation, ')')) {
                if (!$tokens->test(TokenType::Punctuation, ',')) {
                    /** @psalm-suppress MixedAssignment We're purposely adding a mixed value to the parameter list */
                    $parameters[] = $currentToken->value;
                }

                $currentToken = $tokens->next();
            }

            $variableConstraintNode->addChild(new AstNode(AstNodeType::VariableConstraintParameters, $parameters));
            $tokens->expect(
                TokenType::Punctuation,
                ')',
                'Expected closing parenthesis after constraint parameters, got %s'
            );
            $tokens->next();
        }

        $currNode->addChild($variableConstraintNode);
    }
}
