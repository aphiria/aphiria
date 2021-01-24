<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Tokenizers;

/**
 * Defines the argv input tokenizer
 */
final class ArgvInputTokenizer implements IInputTokenizer
{
    /**
     * @inheritdoc
     */
    public function tokenize(string|array $input): array
    {
        $tokens = \is_string($input) ? [$input] : $input;

        // Remove the application name
        \array_shift($tokens);

        /** @var string $token */
        foreach ($tokens as &$token) {
            // We don't use stripslashes() because we want to backslashes when they're not escaping quotes
            $token = \str_replace(["\\'", '\\"'], ["'", '"'], $token);
        }

        return $tokens;
    }
}
