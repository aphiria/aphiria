<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\InvalidUriTemplateException;

/**
 * Defines the interface for trie compilers to implement
 */
interface ITrieCompiler
{
    /**
     * Compiles a route into a trie
     *
     * @param Route $route The route whose template we're compiling
     * @return TrieNode The compiled trie
     * @throws InvalidUriTemplateException Thrown if the template is invalid
     */
    public function compile(Route $route): TrieNode;
}
