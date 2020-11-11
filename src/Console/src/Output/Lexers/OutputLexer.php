<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Lexers;

use RuntimeException;

/**
 * Defines the output lexer
 */
final class OutputLexer implements IOutputLexer
{
    /**
     * @inheritdoc
     */
    public function lex(string $text): array
    {
        $tokens = [];
        $wordBuffer = '';
        $elementNameBuffer = '';
        $inOpenTag = false;
        $inCloseTag = false;
        $charArray = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $textLength = \count($charArray);

        foreach ($charArray as $charIter => $char) {
            switch ($char) {
                case '<':
                    if (self::lookBehind($charArray, $charIter) === '\\') {
                        // This tag was escaped
                        // Don't include the preceding slash
                        $wordBuffer = mb_substr($wordBuffer, 0, -1) . $char;
                    } elseif ($inOpenTag || $inCloseTag) {
                        throw new RuntimeException(
                            sprintf(
                                'Invalid tags near "%s", character #%d',
                                self::getSurroundingText($charArray, $charIter),
                                $charIter
                            )
                        );
                    } else {
                        // Check if this is a closing tag
                        if (self::peek($charArray, $charIter) === '/') {
                            $inCloseTag = true;
                            $inOpenTag = false;
                        } else {
                            $inCloseTag = false;
                            $inOpenTag = true;
                        }

                        // Flush the word buffer
                        if ($wordBuffer !== '') {
                            $tokens[] = new OutputToken(
                                OutputTokenTypes::T_WORD,
                                $wordBuffer,
                                $charIter - mb_strlen($wordBuffer)
                            );
                            $wordBuffer = '';
                        }
                    }

                    break;
                case '>':
                    if ($inOpenTag || $inCloseTag) {
                        if ($inOpenTag) {
                            $tokens[] = new OutputToken(
                                OutputTokenTypes::T_TAG_OPEN,
                                $elementNameBuffer,
                                // Need to get the position of the beginning of the open tag
                                $charIter - mb_strlen($elementNameBuffer) - 1
                            );
                        } else {
                            $tokens[] = new OutputToken(
                                OutputTokenTypes::T_TAG_CLOSE,
                                $elementNameBuffer,
                                // Need to get the position of the beginning of the close tag
                                $charIter - mb_strlen($elementNameBuffer) - 2
                            );
                        }

                        $elementNameBuffer = '';
                        $inOpenTag = false;
                        $inCloseTag = false;
                    } else {
                        $wordBuffer .= $char;
                    }

                    break;
                default:
                    if ($inOpenTag || $inCloseTag) {
                        // We're in a tag, so buffer the element name
                        if ($char !== '/') {
                            $elementNameBuffer .= $char;
                        }
                    } else {
                        // We're outside of a tag somewhere
                        $wordBuffer .= $char;
                    }

                    break;
            }
        }

        // Finish flushing the word buffer
        if ($wordBuffer !== '') {
            $tokens[] = new OutputToken(
                OutputTokenTypes::T_WORD,
                $wordBuffer,
                $textLength - mb_strlen($wordBuffer)
            );
        }

        $tokens[] = new OutputToken(OutputTokenTypes::T_EOF, null, $textLength);

        return $tokens;
    }

    /**
     * Gets text around a certain position for use in exceptions
     *
     * @param string[] $charArray The char array
     * @param int $position The numerical position to grab text around
     * @return string The surrounding text
     */
    private static function getSurroundingText(array $charArray, int $position): string
    {
        if (\count($charArray) <= 3) {
            return implode('', $charArray);
        }

        if ($position <= 3) {
            return implode('', \array_slice($charArray, 0, 4));
        }

        return implode('', \array_slice($charArray, $position - 3, 4));
    }

    /**
     * Looks back at the previous character in the string
     *
     * @param string[] $charArray The char array
     * @param int $currPosition The current position
     * @return string|null The previous character if there is one, otherwise null
     */
    private static function lookBehind(array $charArray, int $currPosition): ?string
    {
        if ($currPosition === 0 || \count($charArray) === 0) {
            return null;
        }

        return $charArray[$currPosition - 1];
    }

    /**
     * Peeks at the next character in the string
     *
     * @param string[] $charArray The char array
     * @param int $currPosition The current position
     * @return string|null The next character if there is one, otherwise null
     */
    private static function peek(array $charArray, int $currPosition): ?string
    {
        $charArrayLength = \count($charArray);

        if ($charArrayLength === 0 || $currPosition === $charArrayLength - 1) {
            return null;
        }

        return $charArray[$currPosition + 1];
    }
}
