<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Trees;

use Opulence\Routing\Route;

/**
 * Defines a trie node that contains a literal segment
 */
class LiteralTrieNode extends TrieNode
{
    /** @var string The value of this node */
    public $value;

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