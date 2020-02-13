<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

/**
 * Defines a root node of a trie
 */
final class RootTrieNode extends TrieNode
{
    /**
     * @inheritdoc
     */
    public function __construct(array $children = [])
    {
        parent::__construct($children, [], null);
    }
}
