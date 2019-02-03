<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Matchers\Trees\Compilers;

use InvalidArgumentException;
use Aphiria\Routing\Matchers\Trees\TrieNode;
use Aphiria\Routing\Route;

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