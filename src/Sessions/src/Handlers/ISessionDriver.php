<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Handlers;

/**
 * Defines the interface for session drivers to implement
 */
interface ISessionDriver
{
    /**
     * Deletes a session from storage
     *
     * @param int|string $sessionId The ID of the session to delete
     */
    public function delete(int|string $sessionId): void;

    /**
     * Performs garbage collection to remove any stale session data
     *
     * @param int $maxLifetime The max lifetime of data allowed in storage
     * @return int The number of deleted sessions
     */
    public function gc(int $maxLifetime): int;

    /**
     * Gets the serialized session data for a particular session
     *
     * @param int|string $sessionId The ID of the session to retrieve from
     * @return string The value of the serialized session
     * @throw OutOfBoundsException Thrown if the session does not exist
     */
    public function get(int|string $sessionId): string;

    /**
     * Persists the session data to storage
     *
     * @param int|string $sessionId The ID of the session to set
     * @param string $sessionData The serialized session data
     */
    public function set(int|string $sessionId, string $sessionData): void;
}
