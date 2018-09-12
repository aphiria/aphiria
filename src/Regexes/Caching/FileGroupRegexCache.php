<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Regexes\Caching;

use Opulence\Routing\Regexes\GroupRegexCollection;

/**
 * Defines the file group regex cache
 */
class FileGroupRegexCache implements IGroupRegexCache
{
    /** @var string The path to the cached group regex file */
    private $path;

    /**
     * @param string $path The path to the cached group regex file
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
            @unlink($this->path);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(): ?GroupRegexCollection
    {
        if (!file_exists($this->path)) {
            return null;
        }

        return unserialize(file_get_contents($this->path));
    }

    /**
     * @inheritdoc
     */
    public function has(): bool
    {
        return file_exists($this->path);
    }

    /**
     * @inheritdoc
     */
    public function set(GroupRegexCollection $regexes): void
    {
        // Clone the routes so that serialization doesn't affect the input regex object
        file_put_contents($this->path, serialize(clone $regexes));
    }
}
