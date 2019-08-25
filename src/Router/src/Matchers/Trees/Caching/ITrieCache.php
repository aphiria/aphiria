<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Matchers\Trees\Caching;

use Aphiria\Routing\Matchers\Trees\TrieNode;

/**
 * Defines the interface for trie caches to implement
 */
interface ITrieCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the trie from cache
     *
     * @return TrieNode|null The trie if it existed in cache, otherwise null
     */
    public function get(): ?TrieNode;

    /**
     * Gets whether or not the trie is cached
     *
     * @return bool True if the trie is cached, otherwise false
     */
    public function has(): bool;

    /**
     * Sets the trie in cache
     *
     * @param TrieNode $trie The trie to cache
     */
    public function set(TrieNode $trie): void;
}
