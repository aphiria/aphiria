<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Lexers;

/**
 * Defines a token created by a lexer
 */
final class Token
{
    /**
     * @param string $type The token type
     * @param mixed $value The token value
     */
    public function __construct(public string $type, public mixed $value)
    {
    }
}
