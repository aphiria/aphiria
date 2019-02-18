<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests\Compilers\Tokenizers;

/**
 * Defines the argv request tokenizer
 */
final class ArgvRequestTokenizer implements IRequestTokenizer
{
    /**
     * @inheritdoc
     */
    public function tokenize($input): array
    {
        // Get rid of the application name
        array_shift($input);

        foreach ($input as &$token) {
            // We don't use stripslashes() because we want to backslashes when they're not escaping quotes
            $token = str_replace(["\\'", '\\"'], ["'", '"'], $token);
        }

        return $input;
    }
}
