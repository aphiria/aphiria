<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

use Aphiria\Console\Output\Lexers\OutputToken;
use Aphiria\Console\Output\Lexers\OutputTokenTypes;
use RuntimeException;

/**
 * Defines the output parser
 */
final class OutputParser implements IOutputParser
{
    /**
     * @inheritdoc
     * @param list<OutputToken> $tokens The list of tokens to parse
     */
    public function parse(array $tokens): AstNode
    {
        $ast = new RootAstNode();
        $currNode = $ast;

        foreach ($tokens as $token) {
            switch ($token->type) {
                case OutputTokenTypes::T_WORD:
                    $currNode?->addChild(new WordAstNode($token->value));

                    break;
                case OutputTokenTypes::T_TAG_OPEN:
                    $childNode = new TagAstNode($token->value);
                    $currNode?->addChild($childNode);
                    $currNode = $childNode;

                    break;
                case OutputTokenTypes::T_TAG_CLOSE:
                    if ($currNode?->value !== $token->value) {
                        throw new RuntimeException(
                            \sprintf(
                                'Improperly nested tag "%s" near character #%d',
                                (string)$token->value,
                                $token->position
                            )
                        );
                    }

                    // Move up one in the tree
                    $currNode = $currNode?->parent;

                    break;
                case OutputTokenTypes::T_EOF:
                    if (!$currNode?->isRoot()) {
                        throw new RuntimeException(
                            \sprintf(
                                'Unclosed %s "%s"',
                                $currNode?->isTag() ? 'tag' : 'node',
                                (string)($currNode?->value ?? 'null')
                            )
                        );
                    }

                    break;
                default:
                    throw new RuntimeException(
                        \sprintf(
                            'Unknown token type "%s" with value "%s" near character #%d',
                            $token->type,
                            (string)$token->value,
                            $token->position
                        )
                    );
            }
        }

        return $ast;
    }
}
