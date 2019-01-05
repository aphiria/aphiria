<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Trees;

/**
 * Defines a root node of a trie
 */
class RootTrieNode extends TrieNode
{
    /**
     * @inheritdoc
     */
    public function __construct(array $children = [])
    {
        parent::__construct($children, [], null);
    }
}