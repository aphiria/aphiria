<?php
namespace Opulence\Router\UriTemplates\Compilers\Parsers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenTypes;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;

/**
 * Defines the URI template parser
 */
class UriTemplateParser implements IUriTemplateParser
{
    /**
     * @inheritdoc
     */
    public function parse(TokenStream $tokens) : AbstractSyntaxTree
    {
        $ast = new AbstractSyntaxTree();

        while (($token = $tokens->getCurrent()) !== null) {
            switch ($token->getType()) {
                case TokenTypes::T_TEXT:
                    $this->parseText($tokens, $ast);
                    break;
                case TokenTypes::T_NUMBER:
                    $this->parseNumber($tokens, $ast);
                    break;
                case TokenTypes::T_VARIABLE:
                    $this->parseVariable($tokens, $ast);
                    break;
                case TokenTypes::T_PUNCTUATION:
                    $this->parsePunctuation($tokens, $ast);
                    break;
                case TokenTypes::T_QUOTED_STRING:
                    // Since we handle a quoted string inside of variables elsewhere, we'll just treat this as text
                    $this->parseText($tokens, $ast);
                    break;
            }
        }

        return $ast;
    }

    /**
     * Parses a punctuation token
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AbstractSyntaxTree $ast The abstract syntax tree to add nodes to
     */
    private function parsePunctuation(TokenStream $tokens, AbstractSyntaxTree $ast) : void
    {
        $token = $tokens->getCurrent();

        switch ($token->getValue()) {
            case '[':
                $parentNodeType = $ast->getCurrentNode()->getType();

                if ($parentNodeType !== NodeTypes::ROOT && $parentNodeType !== NodeTypes::OPTIONAL_ROUTE_PART) {
                    throw new InvalidArgumentException("Unexpected {$token->getType()} \"{$token->getValue()}\"");
                }

                $optionalRoutePartNode = new Node(NodeTypes::OPTIONAL_ROUTE_PART, $token->getValue());
                $ast->getCurrentNode()->addChild($optionalRoutePartNode);
                $ast->setCurrentNode($optionalRoutePartNode);
                $tokens->next();
                break;
            case ']':
                if ($ast->getCurrentNode()->getType() === NodeTypes::OPTIONAL_ROUTE_PART) {
                    // End this optional route part
                    $ast->setCurrentNode($ast->getCurrentNode()->getParent());
                } else {
                    // Just treat this as normal text
                    $ast->getCurrentNode()->addChild(new Node(NodeTypes::TEXT, $token->getValue()));
                }

                $tokens->next();
                break;
            default:
                // Since we handle punctuation inside of variables elsewhere, we'll just treat this as text
                $ast->getCurrentNode()->addChild(new Node(NodeTypes::TEXT, $token->getValue()));
                $tokens->next();
                break;
        }
    }

    /**
     * Parses a number token
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AbstractSyntaxTree $ast The abstract syntax tree to add nodes to
     */
    private function parseNumber(TokenStream $tokens, AbstractSyntaxTree $ast) : void
    {
        $ast->getCurrentNode()->addChild(new Node(NodeTypes::NUMBER, $tokens->getCurrent()->getValue()));
        $tokens->next();
    }

    /**
     * Parses a text token
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AbstractSyntaxTree $ast The abstract syntax tree to add nodes to
     */
    private function parseText(TokenStream $tokens, AbstractSyntaxTree $ast) : void
    {
        $ast->getCurrentNode()->addChild(new Node(NodeTypes::TEXT, $tokens->getCurrent()->getValue()));
        $tokens->next();
    }

    /**
     * Parses a stream of variable tokens
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param AbstractSyntaxTree $ast The abstract syntax tree to add nodes to
     */
    private function parseVariable(TokenStream $tokens, AbstractSyntaxTree $ast) : void
    {
        $variableNode = new Node(NodeTypes::VARIABLE, $tokens->getCurrent()->getValue());
        $ast->getCurrentNode()->addChild($variableNode);
        $ast->setCurrentNode($variableNode);
        $tokens->next();

        // Check for a default value
        if ($tokens->nextIfType(TokenTypes::T_PUNCTUATION, '=')) {
            $tokens->expect(TokenTypes::T_TEXT, null, 'Expected default value for variable, got %s');
            $variableNode->addChild(new Node(NodeTypes::VARIABLE_DEFAULT_VALUE, $tokens->getCurrent()->getValue()));
            $tokens->next();
        }

        // Check for the beginning of a rule list
        if ($tokens->nextIfType(TokenTypes::T_PUNCTUATION, '(')) {
            // Parse all variable rules
            do {
                $this->parseVariableRule($tokens, $variableNode);
            } while ($tokens->nextIfType(TokenTypes::T_PUNCTUATION, ','));

            $tokens->expect(TokenTypes::T_PUNCTUATION, ')', 'Expected closing parenthesis after rules, got %s');
            $tokens->next();
        }

        $ast->setCurrentNode($ast->getCurrentNode()->getParent());
    }

    /**
     * Parses a single variable rule
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @param Node $variableNode The variable node to add nodes to
     */
    private function parseVariableRule(TokenStream $tokens, Node $variableNode) : void
    {
        // Expect a rule name
        $tokens->expect(TokenTypes::T_TEXT, null, 'Expected rule name, got %s');
        $variableRuleNode = new Node(NodeTypes::VARIABLE_RULE, $tokens->getCurrent()->getValue());
        $tokens->next();

        // Check for a parameter list for this rule
        if ($tokens->nextIfType(TokenTypes::T_PUNCTUATION, '(')) {
            $parameters = [];
            $currentToken = $tokens->getCurrent();

            while ($currentToken !== null && !$tokens->test(TokenTypes::T_PUNCTUATION, ')')) {
                if (!$tokens->test(TokenTypes::T_PUNCTUATION, ',')) {
                    $parameters[] = $currentToken->getValue();
                }

                $currentToken = $tokens->next();
            }

            $variableRuleNode->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, $parameters));
            $tokens->expect(TokenTypes::T_PUNCTUATION, ')', 'Expected closing parenthesis after rule parameters, got %s');
            $tokens->next();
        }

        $variableNode->addChild($variableRuleNode);
    }
}
