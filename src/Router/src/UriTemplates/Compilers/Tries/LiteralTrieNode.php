<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;

/**
 * Defines a trie node that contains a literal segment
 */
final class LiteralTrieNode extends TrieNode
{
    /**
     * @param string $value The value of this node
     * @param TrieNode[] $children The list of children
     * @param Route[]|Route $routes The list of routes contained in this segment
     * @param TrieNode|null $hostTrie The host trie, if there is one
     */
    public function __construct(public string $value, array $children, Route|array $routes = [], TrieNode $hostTrie = null)
    {
        parent::__construct($children, $routes, $hostTrie);
    }
}
