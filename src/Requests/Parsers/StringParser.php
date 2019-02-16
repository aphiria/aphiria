<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests\Parsers;

use Aphiria\Console\Requests\IRequest;
use Aphiria\Console\Requests\Tokenizers\StringTokenizer;

/**
 * Defines the string parser
 */
class StringParser extends Parser
{
    /** @var StringTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new StringTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function parse($input): IRequest
    {
        $tokens = $this->tokenizer->tokenize($input);

        return $this->parseTokens($tokens);
    }
}
