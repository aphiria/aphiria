<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Lexers;

/**
 * Defines the interface for URI template lexers to implement
 */
interface IUriTemplateLexer
{
    /**
     * Lexes a raw URI template into a stream of tokens
     * If the template is just a path, it MUST be left-padded with a '/'
     *
     * @param string $uriTemplate The raw URI template to lex
     * @return TokenStream The stream of lexed tokens
     * @throws LexingException Thrown if the template was incorrectly formatted
     */
    public function lex(string $uriTemplate): TokenStream;
}
