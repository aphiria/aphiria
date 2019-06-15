<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/Aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Matchers\Trees\Caching;

use Aphiria\Routing\Matchers\Trees\TrieNode;

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
