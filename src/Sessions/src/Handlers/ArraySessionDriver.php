<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Handlers;

use OutOfBoundsException;

/**
 * Defines the session driver backed by an in-memory array
 */
final class ArraySessionDriver implements ISessionDriver
{
    /** @var array The session data */
    private array $sessionData = [];

    /**
     * @inheritdoc
     */
    public function delete($sessionId): void
    {
        unset($this->sessionData[$sessionId]);
    }

    /**
     * @inheritdoc
     */
    public function gc(int $maxLifetime): void
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function get($sessionId): string
    {
        if (!isset($this->sessionData[$sessionId])) {
            throw new OutOfBoundsException("Session with ID $sessionId does not exist");
        }

        return $this->sessionData[$sessionId];
    }

    /**
     * @inheritDoc
     */
    public function set($sessionId, string $sessionData): void
    {
        $this->sessionData[$sessionId] = $sessionData;
    }
}
