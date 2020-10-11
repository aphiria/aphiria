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
 * Defines the session driver backed by an in-memory array
 */
final class ArraySessionDriver implements ISessionDriver
{
    /** @var array The session data */
    private array $sessionData = [];

    /**
     * @inheritdoc
     */
    public function delete(int|string $sessionId): void
    {
        unset($this->sessionData[$sessionId]);
    }

    /**
     * @inheritdoc
     */
    public function gc(int $maxLifetime): void
    {
    }

    /**
     * @inheritdoc
     */
    public function get(int|string $sessionId): string
    {
        if (!isset($this->sessionData[$sessionId])) {
            throw new OutOfBoundsException("Session with ID $sessionId does not exist");
        }

        return $this->sessionData[$sessionId];
    }

    /**
     * @inheritdoc
     */
    public function set(int|string $sessionId, string $sessionData): void
    {
        $this->sessionData[$sessionId] = $sessionData;
    }
}
