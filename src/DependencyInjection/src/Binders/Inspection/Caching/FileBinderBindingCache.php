<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Inspection\Caching;

/**
 * Defines the binder binding cache that uses a file as the cache
 */
final class FileBinderBindingCache implements IBinderBindingCache
{
    /** @var string The cache file path */
    private string $filePath;

    /**
     * @param string $filePath The cache file path
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        if (\file_exists($this->filePath)) {
            @unlink($this->filePath);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(): ?array
    {
        $rawContents = @\file_get_contents($this->filePath);

        if ($rawContents === false) {
            return null;
        }

        return \unserialize(\base64_decode($rawContents));
    }

    /**
     * @inheritdoc
     */
    public function set(array $bindings): void
    {
        \file_put_contents($this->filePath, \base64_encode(\serialize($bindings)));
    }
}
