<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Trees;

use Aphiria\Routing\Route;

/**
 * Defines a trie node that contains a literal segment
 */
final class LiteralTrieNode extends TrieNode
{
    /** @var string The value of this node */
    public string $value;

    /**
     * @param string $value The value of this node
     * @param TrieNode[] $children The list of children
     * @param Route[] $routes The list of routes contained in this segment
     * @param TrieNode|null $hostTrie The host trie, if there is one
     */
    public function __construct(string $value, array $children, $routes = [], TrieNode $hostTrie = null)
    {
        parent::__construct($children, $routes, $hostTrie);

        $this->value = $value;
    }
}
