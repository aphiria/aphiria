<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Configuration;

use Closure;
use RuntimeException;

/**
 * Defines the interface for configurations to implement
 */
interface IConfiguration
{
    /**
     * Gets the array value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return array<mixed, mixed> The value at the path
     * @throws MissingConfigurationValueException Thrown if there was no value at the input path
     */
    public function getArray(string $path): array;

    /**
     * Gets the boolean value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return bool The value at the path
     * @throws MissingConfigurationValueException Thrown if there was no value at the input path
     */
    public function getBool(string $path): bool;

    /**
     * Gets the float value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return float The value at the path
     * @throws MissingConfigurationValueException Thrown if there was no value at the input path
     */
    public function getFloat(string $path): float;

    /**
     * Gets the int value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return int The value at the path
     * @throws MissingConfigurationValueException Thrown if there was no value at the input path
     */
    public function getInt(string $path): int;

    /**
     * Gets the object value at the path
     *
     * @template T of object
     * @param string $path The period-delimited path to the value in the config to get
     * @param Closure(mixed): T $factory The factory that will take in the raw config value and return the object
     * @return T The object at the path
     * @throws RuntimeException Thrown if the factory does not return an object
     * @throws MissingConfigurationValueException Thrown if there was no value at the input path
     */
    public function getObject(string $path, Closure $factory): object;

    /**
     * Gets the string value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return string The value at the path
     * @throws MissingConfigurationValueException Thrown if there was no value at the input path
     */
    public function getString(string $path): string;

    /**
     * Gets the value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return mixed The value at the path
     * @throws MissingConfigurationValueException Thrown if there was no value at the input path
     */
    public function getValue(string $path): mixed;

    /**
     * Tries to get an array value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param array|null $value The value if one was found, otherwise null
     * @param-out array $value
     * @return bool True if the value existed, otherwise false
     */
    public function tryGetArray(string $path, ?array &$value): bool;

    /**
     * Tries to get a boolean value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param bool|null $value The value if one was found, otherwise null
     * @param-out bool $value
     * @return bool True if the value existed, otherwise false
     */
    public function tryGetBool(string $path, ?bool &$value): bool;

    /**
     * Tries to get a float value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param float|null $value The value if one was found, otherwise null
     * @param-out float $value
     * @return bool True if the value existed, otherwise false
     */
    public function tryGetFloat(string $path, ?float &$value): bool;

    /**
     * Tries to get an integer value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param int|null $value The value if one was found, otherwise null
     * @param-out int $value
     * @return bool True if the value existed, otherwise false
     */
    public function tryGetInt(string $path, ?int &$value): bool;

    /**
     * Tries to get the object value at the path
     *
     * @template T of object
     * @param string $path The period-delimited path to the value in the config to get
     * @param Closure(mixed): T $factory The factory that will take in the raw config value and return the object
     * @param T|null $value The value if one was found, otherwise null
     * @param-out T $value
     * @return bool True if the value existed, otherwise false
     * @throws RuntimeException Thrown if the factory does not return an object
     */
    public function tryGetObject(string $path, Closure $factory, ?object &$value): bool;

    /**
     * Tries to get a string value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param string|null $value The value if one was found, otherwise null
     * @param-out string $value
     * @return bool True if the value existed, otherwise false
     */
    public function tryGetString(string $path, ?string &$value): bool;

    /**
     * Tries to get a value value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param mixed $value The value if one was found, otherwise null
     * @param-out mixed $value
     * @return bool True if the value existed, otherwise false
     */
    public function tryGetValue(string $path, mixed &$value): bool;
}
