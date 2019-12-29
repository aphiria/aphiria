<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Caching;

use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;

/**
 * Defines the constraint registry cache backed by file storage
 */
final class FileObjectConstraintRegistryCache implements IObjectConstraintRegistryCache
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
     * @inheritoc
     */
    public function get(): ?ObjectConstraintsRegistry
    {
        if (!\file_exists($this->path)) {
            return null;
        }

        return \unserialize(\file_get_contents($this->path));
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
        /**
         * Under the hood, Opis will actually mutate the properties of the constraints so that closures can be serialized
         * properly.  To make sure we're not changing the properties in the input constraints, we clone it before serialization.
         */
        \file_put_contents($this->path, \serialize(clone $objectConstraints));
    }
}
