<?php
namespace Opulence\Routing\Matchers\Caching;

use Opulence\Routing\Matchers\RouteCollection;

/**
 * Defines the file route cache
 */
class FileRouteCache implements IRouteCache
{
    /** @var string The path to the cached route file */
    private $path = '';

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
    public function flush() : void
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
    public function set(RouteCollection $routes): void
    {
        // Clone the routes so that serialization doesn't affect the input routes object
        file_put_contents($this->path, serialize(clone $routes));
    }
}
