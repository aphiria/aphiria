<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use InvalidArgumentException;

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
     * @throws InvalidArgumentException Thrown if the template is invalid
     */
    public function compile(Route $route): TrieNode;
}
