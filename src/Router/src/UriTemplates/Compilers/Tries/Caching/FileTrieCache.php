<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries\Caching;

use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;

/**
 * Defines the file trie cache
 */
final class FileTrieCache implements ITrieCache
{
    /** @var string The path to the cache file */
    private string $path;

    /**
     * @param string $path The path to the cache file
     */
    public function __construct(string $path)
    {
        $this->path = $path;
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

        return \unserialize(\file_get_contents($this->path));
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
