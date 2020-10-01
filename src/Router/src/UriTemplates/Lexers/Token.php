<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Lexers;

/**
 * Defines a token created by a lexer
 */
final class Token
{
    /** @var string The token type */
    public string $type;
    /** @var mixed The token value */
    public mixed $value;

    /**
     * @param string $type The token type
     * @param mixed $value The token value
     */
    public function __construct(string $type, mixed $value)
    {
        $this->type = $type;
        $this->value = $value;
    }
}
