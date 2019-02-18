<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output\Compilers\Parsers;

use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputToken;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputTokenTypes;
use Aphiria\Console\Output\Compilers\Parsers\Nodes\TagNode;
use Aphiria\Console\Output\Compilers\Parsers\Nodes\WordNode;
use RuntimeException;

/**
 * Defines the output parser
 */
final class OutputParser implements IOutputParser
{
    /**
     * @inheritdoc
     * @param OutputToken[] $tokens The list of tokens to parse
     */
    public function parse(array $tokens): AbstractSyntaxTree
    {
        $ast = new AbstractSyntaxTree();

        foreach ($tokens as $token) {
            switch ($token->type) {
                case OutputTokenTypes::T_WORD:
                    $ast->getCurrentNode()->addChild(new WordNode($token->value));

                    break;
                case OutputTokenTypes::T_TAG_OPEN:
                    $childNode = new TagNode($token->value);
                    $ast->getCurrentNode()->addChild($childNode);
                    $ast->setCurrentNode($childNode);

                    break;
                case OutputTokenTypes::T_TAG_CLOSE:
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
                case OutputTokenTypes::T_EOF:
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
