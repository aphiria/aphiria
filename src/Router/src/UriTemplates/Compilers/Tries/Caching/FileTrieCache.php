<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries\Caching;

use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;
use RuntimeException;

/**
 * Defines the file trie cache
 */
final class FileTrieCache implements ITrieCache
{
    /**
     * @param string $path The path to the cache file
     */
    public function __construct(private string $path)
    {
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        if ($this->has()) {
            @\unlink($this->path);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(): ?TrieNode
    {
        if (!\file_exists($this->path)) {
            return null;
        }

        $trie = \unserialize(\file_get_contents($this->path));

        if ($trie !== null && !$trie instanceof TrieNode) {
            throw new RuntimeException('Trie must be instance of ' . TrieNode::class . ' or null');
        }

        return $trie;
    }

    /**
     * @inheritdoc
     */
    public function has(): bool
    {
        return \file_exists($this->path);
    }

    /**
     * @inheritdoc
     */
    public function set(TrieNode $trie): void
    {
        \file_put_contents($this->path, \serialize($trie));
    }
}
