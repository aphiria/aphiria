<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Input\Compilers\Tokenizers;

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
        if ($input === null) {
            $input = $_SERVER['argv'];
        }

        if (!is_array($input)) {
            throw new InvalidArgumentException(self::class . ' only accepts arrays as input');
        }

        // Get rid of the application name
        array_shift($input);

        foreach ($input as &$token) {
            // We don't use stripslashes() because we want to backslashes when they're not escaping quotes
            $token = str_replace(["\\'", '\\"'], ["'", '"'], $token);
        }

        return $input;
    }
}
