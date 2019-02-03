<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Matchers\Trees;

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