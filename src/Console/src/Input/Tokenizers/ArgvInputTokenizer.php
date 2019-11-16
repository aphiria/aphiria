<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Tokenizers;

use InvalidArgumentException;

/**
 * Defines the argv input tokenizer
 */
final class ArgvInputTokenizer implements IInputTokenizer
{
    /**
     * @inheritdoc
     */
    public function tokenize($input): array
    {
        $tokens = $input;

        if ($tokens === null) {
            $tokens = $_SERVER['argv'];
        }

        if (!is_array($tokens)) {
            throw new InvalidArgumentException(self::class . ' only accepts arrays as input');
        }

        // Remove the application name
        array_shift($tokens);

        foreach ($tokens as &$token) {
            // We don't use stripslashes() because we want to backslashes when they're not escaping quotes
            $token = str_replace(["\\'", '\\"'], ["'", '"'], $token);
        }

        return $tokens;
    }
}
