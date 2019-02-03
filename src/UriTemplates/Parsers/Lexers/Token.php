<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\UriTemplates\Parsers\Lexers;

/**
 * Defines a token created by a lexer
 */
class Token
{
    /** @var string The token type */
    public $type;
    /** @var mixed The token value */
    public $value;

    /**
     * @param string $type The token type
     * @param mixed $value The token value
     */
    public function __construct(string $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }
}
