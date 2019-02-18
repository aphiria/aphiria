<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Compilers\Parsers;

use Aphiria\Console\Responses\Compilers\Lexers\Tokens\Token;
use Aphiria\Console\Responses\Compilers\Lexers\Tokens\TokenTypes;
use Aphiria\Console\Responses\Compilers\Parsers\Nodes\TagNode;
use Aphiria\Console\Responses\Compilers\Parsers\Nodes\WordNode;
use RuntimeException;

/**
 * Defines the response parser
 */
final class ResponseParser implements IResponseParser
{
    /**
     * @inheritdoc
     * @param Token[] $tokens The list of tokens to parse
     */
    public function parse(array $tokens): AbstractSyntaxTree
    {
        $ast = new AbstractSyntaxTree();

        foreach ($tokens as $token) {
            switch ($token->type) {
                case TokenTypes::T_WORD:
                    $ast->getCurrentNode()->addChild(new WordNode($token->value));

                    break;
                case TokenTypes::T_TAG_OPEN:
                    $childNode = new TagNode($token->value);
                    $ast->getCurrentNode()->addChild($childNode);
                    $ast->setCurrentNode($childNode);

                    break;
                case TokenTypes::T_TAG_CLOSE:
                    if ($ast->getCurrentNode()->value != $token->value) {
                        throw new RuntimeException(
                            sprintf(
                                'Improperly nested tag "%s" near character #%d',
                                $token->value,
                                $token->position
                            )
                        );
                    }

                    // Move up one in the tree
                    $ast->setCurrentNode($ast->getCurrentNode()->parent);

                    break;
                case TokenTypes::T_EOF:
                    if (!$ast->getCurrentNode()->isRoot()) {
                        throw new RuntimeException(
                            sprintf(
                                'Unclosed %s "%s"',
                                $ast->getCurrentNode()->isTag() ? 'tag' : 'node',
                                $ast->getCurrentNode()->value
                            )
                        );
                    }

                    break;
                default:
                    throw new RuntimeException(
                        sprintf(
                            'Unknown token type "%s" with value "%s" near character #%d',
                            $token->type,
                            $token->value,
                            $token->position
                        )
                    );
            }
        }

        return $ast;
    }
}
