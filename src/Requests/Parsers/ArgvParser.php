<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests\Parsers;

use InvalidArgumentException;
use Aphiria\Console\Requests\IRequest;
use Aphiria\Console\Requests\Tokenizers\ArgvTokenizer;

/**
 * Defines the argv parser
 */
class ArgvParser extends Parser
{
    /** @var ArgvTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new ArgvTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function parse($input): IRequest
    {
        if ($input === null) {
            $input = $_SERVER['argv'];
        }

        if (!is_array($input)) {
            throw new InvalidArgumentException('ArgvParser parser only accepts arrays as input');
        }

        $tokens = $this->tokenizer->tokenize($input);

        return $this->parseTokens($tokens);
    }
}
