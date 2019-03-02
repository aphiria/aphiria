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
    /** @const The default name of the Aphiria application */
    private const DEFAULT_APPLICATION_NAME = 'aphiria';
    /** @var string The name of the application as it appears in the console */
    private $applicationName;

    /**
     * @param string $applicationName The name of the application as it appears in the console
     */
    public function __construct(string $applicationName = self::DEFAULT_APPLICATION_NAME)
    {
        $this->applicationName = $applicationName;
    }

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

        if (count($input) > 0 && $input[0] === $this->applicationName) {
            /**
             * Having to prepend the application name when manually invoking a command is verbose.  To make it easier,
             * you don't have to prepend the input with the application name.  However, in the case this is really
             * coming from $argv, the application name will be prepended.  So, we only remove it from the input if it
             * is there.
             */
            array_shift($input);
        }

        foreach ($input as &$token) {
            // We don't use stripslashes() because we want to backslashes when they're not escaping quotes
            $token = str_replace(["\\'", '\\"'], ["'", '"'], $token);
        }

        return $input;
    }
}
