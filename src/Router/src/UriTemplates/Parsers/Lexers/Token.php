<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers\Lexers;

/**
 * Defines a token created by a lexer
 */
final class Token
{
    /** @var string The token type */
    public string $type;
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
