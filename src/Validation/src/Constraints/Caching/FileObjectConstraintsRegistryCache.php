<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Caching;

use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use RuntimeException;

/**
 * Defines the constraint registry cache backed by file storage
 */
final class FileObjectConstraintsRegistryCache implements IObjectConstraintsRegistryCache
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
     * @inheritoc
     */
    public function get(): ?ObjectConstraintsRegistry
    {
        if (!\file_exists($this->path)) {
            return null;
        }

        $constraints = \unserialize(\file_get_contents($this->path));

        if ($constraints !== null && !$constraints instanceof ObjectConstraintsRegistry) {
            throw new RuntimeException('Constraints must be instance of ' . ObjectConstraintsRegistry::class . ' or null');
        }

        return $constraints;
    }

    /**
     * @inheritoc
     */
    public function has(): bool
    {
        return \file_exists($this->path);
    }

    /**
     * @inheritdoc
     */
    public function set(ObjectConstraintsRegistry $objectConstraints): void
    {
        \file_put_contents($this->path, \serialize($objectConstraints));
    }
}
