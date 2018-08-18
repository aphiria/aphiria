<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Caching;

use Opulence\Routing\ClosureRouteAction;
use Opulence\Routing\MethodRouteAction;
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\Route;
use Opulence\Routing\RouteAction;
use Opulence\Routing\RouteCollection;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Defines the file route cache
 */
class FileRouteCache implements IRouteCache
{
    /** @var string The path to the cached route file */
    private $path;

    /**
     * @param string $path The path to the cached route file
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
    public function get(): ?RouteCollection
    {
        if (!file_exists($this->path)) {
            return null;
        }

        return unserialize(
            file_get_contents($this->path),
            [
                'allowed_classes' => [
                    RouteAction::class,
                    ClosureRouteAction::class,
                    MethodRouteAction::class,
                    MiddlewareBinding::class,
                    Route::class,
                    RouteCollection::class,
                    UriTemplate::class
                ]
            ]
        );
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
    public function set(RouteCollection $routes): void
    {
        // Clone the routes so that serialization doesn't affect the input routes object
        file_put_contents($this->path, serialize(clone $routes));
    }
}
