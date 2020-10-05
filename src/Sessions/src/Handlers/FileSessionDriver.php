<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Handlers;

use OutOfBoundsException;

/**
 * Defines a session driver backed by file storage
 */
final class FileSessionDriver implements ISessionDriver
{
    /**
     * @param string $basePath The base path to the session storage files
     */
    public function __construct(private string $basePath)
    {
    }

    /**
     * @inheritdoc
     */
    public function delete(int|string $sessionId): void
    {
        @unlink("{$this->basePath}/$sessionId");
    }

    /**
     * @inheritdoc
     */
    public function gc(int $maxLifetime): void
    {
        $sessionFiles = glob("{$this->basePath}/*", GLOB_NOSORT);
        $limit = time() - $maxLifetime;

        foreach ($sessionFiles as $sessionFile) {
            if (filemtime($sessionFile) < $limit) {
                @unlink($sessionFile);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get(int|string $sessionId): string
    {
        if (!\file_exists($sessionPath = "{$this->basePath}/$sessionId")) {
            throw new OutOfBoundsException("Session with ID $sessionId does not exist");
        }

        return \file_get_contents($sessionPath);
    }

    /**
     * @inheritdoc
     */
    public function set(int|string $sessionId, string $sessionData): void
    {
        \file_put_contents("{$this->basePath}/$sessionId", $sessionData, LOCK_EX);
    }
}
