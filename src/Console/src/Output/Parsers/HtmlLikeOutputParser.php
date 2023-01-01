<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

use Aphiria\Console\Output\Lexers\OutputToken;
use Aphiria\Console\Output\Lexers\OutputTokenType;
use RuntimeException;

/**
 * Defines the HTML-like output parser
 */
final class HtmlLikeOutputParser implements IOutputParser
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
                case OutputTokenType::Word:
                    $currNode?->addChild(new WordAstNode($token->value));

                    break;
                case OutputTokenType::TagOpen:
                    $childNode = new TagAstNode($token->value);
                    $currNode?->addChild($childNode);
                    $currNode = $childNode;

                    break;
                case OutputTokenType::TagClose:
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
                case OutputTokenType::Eof:
                    if ($currNode === null || !$currNode->isRoot()) {
                        throw new RuntimeException(
                            \sprintf(
                                'Unclosed %s "%s"',
                                $currNode?->isTag() ? 'tag' : 'node',
                                (string)($currNode?->value ?? 'null')
                            )
                        );
                    }

                    break;
            }
        }

        return $ast;
    }
}
