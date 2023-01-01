<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Caching;

use Aphiria\Routing\RouteCollection;
use RuntimeException;

/**
 * Defines the route cache that uses a local file
 */
final class FileRouteCache implements IRouteCache
{
    /**
     * @param string $path The path to the cache file
     */
    public function __construct(private readonly string $path)
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
    public function get(): ?RouteCollection
    {
        if (!\file_exists($this->path)) {
            return null;
        }

        $routes = \unserialize(\file_get_contents($this->path));

        if ($routes !== null && !$routes instanceof RouteCollection) {
            throw new RuntimeException('Routes must be instance of ' . RouteCollection::class . ' or null');
        }

        return $routes;
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
    public function set(RouteCollection $routes): void
    {
        \file_put_contents($this->path, \serialize($routes));
    }
}
