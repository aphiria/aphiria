<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Trees\Compilers;

use InvalidArgumentException;
use Opulence\Routing\Matchers\Trees\TrieNode;
use Opulence\Routing\Route;

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