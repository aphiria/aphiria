<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Parsers\Lexers;

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
