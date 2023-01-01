<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Lexers;

/**
 * Defines a token created by a lexer
 */
final readonly class Token
{
    /**
     * @param TokenType $type The token type
     * @param mixed $value The token value
     */
    public function __construct(public TokenType $type, public mixed $value)
    {
    }
}
