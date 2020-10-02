<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions;

use ArrayAccess;

/**
 * Defines the interface for sessions to implement
 */
interface ISession extends ArrayAccess
{
    /**
     * Marks newly flashed data as old, and old data is deleted
     */
    public function ageFlashData(): void;

    /**
     * Gets whether or not a session variable is set
     *
     * @param string $key The name of the variable to search for
     * @return bool True if the session has a variable, otherwise false
     */
    public function containsKey(string $key): bool;

    /**
     * Deletes a variable
     *
     * @param string $key The name of the variable to delete
     */
    public function delete(string $key): void;

    /**
     * Flashes data for exactly one request
     *
     * @param string $key The name of the variable to set
     * @param mixed $value The value of the variable
     */
    public function flash(string $key, mixed $value): void;

    /**
     * Flushes all the session variables
     */
    public function flush(): void;

    /**
     * Gets the value of a variable
     *
     * @param string $key The name of the variable to get
     * @param mixed $defaultValue The default value to use if the variable does not exist
     * @return mixed The value of the variable if it exists, otherwise the default value
     */
    public function get(string $key, mixed $defaultValue = null): mixed;

    /**
     * Gets the mapping of all session variable names to their values
     *
     * @return array The list of all session variables
     */
    public function getAll(): array;

    /**
     * Gets the session Id
     *
     * @return int|string The session Id
     */
    public function getId(): int|string;

    /**
     * Reflashes all of the flash data
     */
    public function reflash(): void;

    /**
     * Regenerates the Id
     */
    public function regenerateId(): void;

    /**
     * Sets the value of a variable
     *
     * @param string $key The name of the variable to set
     * @param mixed $value The value of the variable
     */
    public function set(string $key, mixed $value): void;

    /**
     * Sets the session Id
     *
     * @param int|string $id The session Id
     */
    public function setId(int|string $id): void;

    /**
     * Sets the value of many variables
     * This will merge the variables into the already-existing variables
     * If a variable already exists, its value will be overwritten
     *
     * @param array $variables The name => value pairings of session variables
     */
    public function setMany(array $variables): void;
}
