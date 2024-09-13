<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions;

use ArrayAccess;

/**
 * Defines the interface for sessions to implement
 *
 * @extends ArrayAccess<string, mixed>
 */
interface ISession extends ArrayAccess
{
    /** @var array<string, mixed> The mapping of all session variable names to values */
    public array $variables { get; }
    /** @var int|string The session Id */
    public int|string $id { get; set; }

    /**
     * Adds many variables
     * This will merge the variables into the already-existing variables
     * If a variable already exists, its value will be overwritten
     *
     * @param array<string, mixed> $variables The name => value pairings of session variables
     */
    public function addManyVariables(array $variables): void;

    /**
     * Marks newly flashed data as old, and old data is deleted
     */
    public function ageFlashData(): void;

    /**
     * Gets whether or not a session variable is set
     *
     * @param string $name The name of the variable to search for
     * @return bool True if the session has a variable, otherwise false
     */
    public function containsKey(string $name): bool;

    /**
     * Deletes a variable
     *
     * @param string $name The name of the variable to delete
     */
    public function delete(string $name): void;

    /**
     * Flashes data for exactly one request
     *
     * @param string $name The name of the variable to set
     * @param mixed $value The value of the variable
     */
    public function flash(string $name, mixed $value): void;

    /**
     * Flushes all the session variables
     */
    public function flush(): void;

    /**
     * Gets the value of a variable
     *
     * @param string $name The name of the variable to get
     * @param mixed $defaultValue The default value to use if the variable does not exist
     * @return mixed The value of the variable if it exists, otherwise the default value
     */
    public function getVariable(string $name, mixed $defaultValue = null): mixed;

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
     * @param string $name The name of the variable to set
     * @param mixed $value The value of the variable
     */
    public function setVariable(string $name, mixed $value): void;
}
