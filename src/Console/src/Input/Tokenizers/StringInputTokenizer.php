<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Tokenizers;

use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the string input tokenizer
 */
final class StringInputTokenizer implements IInputTokenizer
{
    /**
     * @inheritdoc
     */
    public function tokenize(string|array $input): array
    {
        if (!\is_string($input)) {
            throw new InvalidArgumentException('Input must be a string');
        }

        $inDoubleQuotes = false;
        $inSingleQuotes = false;
        $charArray = preg_split('//u', trim($input), -1, PREG_SPLIT_NO_EMPTY);
        $previousChar = '';
        $buffer = '';
        $tokens = [];

        foreach ($charArray as $charIter => $char) {
            switch ($char) {
                case '"':
                    // If the double quote is inside single quotes, we treat it as part of a quoted string
                    if (!$inSingleQuotes) {
                        $inDoubleQuotes = !$inDoubleQuotes;
                    }

                    $buffer .= '"';

                    break;
                case "'":
                    // If the single quote is inside double quotes, we treat it as part of a quoted string
                    if (!$inDoubleQuotes) {
                        $inSingleQuotes = !$inSingleQuotes;
                    }

                    $buffer .= "'";

                    break;
                default:
                    if ($inDoubleQuotes || $inSingleQuotes || $char !== ' ') {
                        $buffer .= $char;
                    } elseif ($previousChar !== ' ' && $buffer !== '') {
                        // We've hit a space outside a quoted string, so flush the buffer
                        $tokens[] = $buffer;
                        $buffer = '';
                    }
            }

            $previousChar = $char;
        }

        // Flush out the buffer
        if ($buffer !== '') {
            $tokens[] = $buffer;
        }

        if ($inDoubleQuotes || $inSingleQuotes) {
            throw new RuntimeException('Unclosed ' . ($inDoubleQuotes ? 'double' : 'single') . ' quote');
        }

        return $tokens;
    }
}
