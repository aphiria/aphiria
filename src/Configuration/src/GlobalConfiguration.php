<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration;

use RuntimeException;

/**
 * Defines the global configuration
 */
class GlobalConfiguration
{
    /** @var IConfiguration|null The underlying static instance of this class */
    private static ?IConfiguration $instance = null;

    /**
     * Gets the array value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return array The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function getArray(string $path): array
    {
        self::validateInstanceSet();

        return self::$instance->getArray($path);
    }

    /**
     * Gets the boolean value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return bool The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function getBool(string $path): bool
    {
        self::validateInstanceSet();

        return self::$instance->getBool($path);
    }

    /**
     * Gets the float value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return float The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function getFloat(string $path): float
    {
        self::validateInstanceSet();

        return self::$instance->getFloat($path);
    }

    /**
     * Gets the int value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return int The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function getInt(string $path): int
    {
        self::validateInstanceSet();

        return self::$instance->getInt($path);
    }

    /**
     * Gets the string value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return string The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function getString(string $path): string
    {
        self::validateInstanceSet();

        return self::$instance->getString($path);
    }

    /**
     * Gets the value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return mixed The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function getValue(string $path)
    {
        self::validateInstanceSet();

        return self::$instance->getValue($path);
    }

    /**
     * Sets the global configuration instance
     *
     * @param IConfiguration $configuration The configuration to use as the global configuration
     */
    public static function setInstance(IConfiguration $configuration): void
    {
        self::$instance = $configuration;
    }

    /**
     * Tries to get an array value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param array|null $value The value if one was found, otherwise null
     * @return bool True if the value existed, otherwise false
     */
    public static function tryGetArray(string $path, ?array &$value): bool
    {
        self::validateInstanceSet();

        return self::$instance->tryGetArray($path, $value);
    }

    /**
     * Tries to get a boolean value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param bool|null $value The value if one was found, otherwise null
     * @return bool True if the value existed, otherwise false
     */
    public static function tryGetBool(string $path, ?bool &$value): bool
    {
        self::validateInstanceSet();

        return self::$instance->tryGetBool($path, $value);
    }

    /**
     * Tries to get a float value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param float|null $value The value if one was found, otherwise null
     * @return bool True if the value existed, otherwise false
     */
    public static function tryGetFloat(string $path, ?float &$value): bool
    {
        self::validateInstanceSet();

        return self::$instance->tryGetFloat($path, $value);
    }

    /**
     * Tries to get an integer value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param int|null $value The value if one was found, otherwise null
     * @return bool True if the value existed, otherwise false
     */
    public static function tryGetInt(string $path, ?int &$value): bool
    {
        self::validateInstanceSet();

        return self::$instance->tryGetInt($path, $value);
    }

    /**
     * Tries to get a string value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param string|null $value The value if one was found, otherwise null
     * @return bool True if the value existed, otherwise false
     */
    public static function tryGetString(string $path, ?string &$value): bool
    {
        self::validateInstanceSet();

        return self::$instance->tryGetString($path, $value);
    }

    /**
     * Tries to get a value value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @param mixed|null $value The value if one was found, otherwise null
     * @return bool True if the value existed, otherwise false
     */
    public static function tryGetValue(string $path, &$value): bool
    {
        self::validateInstanceSet();

        return self::$instance->tryGetValue($path, $value);
    }

    /**
     * Validates that an instance is set
     *
     * @throws RuntimeException Thrown if no instance was set
     */
    private static function validateInstanceSet(): void
    {
        if (self::$instance === null) {
            throw new RuntimeException('Must call ' . self::class . '::setInstance() before calling getValue()');
        }
    }
}
