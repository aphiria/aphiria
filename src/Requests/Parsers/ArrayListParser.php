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
use Aphiria\Console\Requests\Tokenizers\ArrayListTokenizer;

/**
 * Defines the array list parser
 */
class ArrayListParser extends Parser
{
    /** @var ArrayListTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new ArrayListTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function parse($input): IRequest
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException(__METHOD__ . ' only accepts arrays as input');
        }

        $tokens = $this->tokenizer->tokenize($input);

        return $this->parseTokens($tokens);
    }
}
