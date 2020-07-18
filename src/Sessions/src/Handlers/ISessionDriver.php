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
    public function delete($sessionId): void;

    /**
     * Performs garbage collection to remove any stale session data
     *
     * @param int $maxLifetime The max lifetime of data allowed in storage
     */
    public function gc(int $maxLifetime): void;

    /**
     * Gets the serialized session data for a particular session
     *
     * @param int|string $sessionId The ID of the session to retrieve from
     * @return mixed The value of the serialized session
     * @throw OutOfBoundsException Thrown if the session does not exist
     */
    public function get($sessionId): string;

    /**
     * Persists the session data to storage
     *
     * @param int|string $sessionId The ID of the session to set
     * @param string $sessionData The serialized session data
     */
    public function set($sessionId, string $sessionData): void;
}
