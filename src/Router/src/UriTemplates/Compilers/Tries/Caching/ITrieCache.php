<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries\Caching;

use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;
use RuntimeException;

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
     * @throws RuntimeException Thrown if the trie isn't the expected type
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
