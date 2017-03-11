<?php
namespace Opulence\Router\UriTemplates\Parsers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Router\UriTemplates\Parsers\Lexers\Tokens\TokenTypes;
use Opulence\Router\UriTemplates\Parsers\Nodes\Node;
use Opulence\Router\UriTemplates\Parsers\Nodes\NodeTypes;

/**
 * Defines the URI template parser
 */
class UriTemplateParser
{
    public function parse(TokenStream $tokens) : AbstractSyntaxTree
    {
        $ast = new AbstractSyntaxTree();
        
        while (($token = $tokens->getCurrent()) !== null) {
            switch ($token->getType()) {
                case TokenTypes::T_TEXT:
                    $ast->getCurrentNode()->addChild(new Node(NodeTypes::TEXT, $token->getValue()));
                    break;
                case TokenTypes::T_VARIABLE:
                    $variableNode = new Node(NodeTypes::VARIABLE, $token->getValue());
                    $ast->getCurrentNode()->addChild($variableNode);
                    $ast->setCurrentNode($variableNode);
                    break;
                case TokenTypes::T_PUNCTUATION:
                    switch ($token->getValue()) {
                        case '[':
                            // Todo
                            break;
                        case ']':
                            // Todo
                            break;
                        case '(':
                            // Todo
                            break;
                        case ')':
                            // Todo
                            break;
                        case '=':
                            // Todo
                            break;
                        case ',':
                            // Todo
                            break;
                        default:
                            throw new InvalidArgumentException(
                                "Unexpected {$token->getType()} \"{$token->getValue()}\""
                            );
                    }
                    
                    break;
                case TokenTypes::T_QUOTED_STRING:
                    // Todo
                    break;
            }
        }
        
        return $ast;
    }
}
