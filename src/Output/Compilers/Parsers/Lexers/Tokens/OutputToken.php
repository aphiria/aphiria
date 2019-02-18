<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens;

/**
 * Defines a output token
 */
final class OutputToken
{
    /** @var int The token type */
    public $type;
    /** @var mixed The value of the token */
    public $value;
    /** @var int The position of the token in the original text */
    public $position;

    /**
     * @param string $type The token type
     * @param mixed $value The value of the token
     * @param int $position The position of the token in the original text
     */
    public function __construct(string $type, $value, int $position)
    {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }
}
