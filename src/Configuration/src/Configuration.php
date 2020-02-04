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
 * Defines a wrapper around an application's raw configuration
 */
class Configuration
{
    /** @var Configuration|null The underlying static instance of this class */
    private static ?Configuration $instance = null;
    /** @var array The raw config */
    private array $rawConfig;

    /**
     * @param array $rawConfig The raw config
     */
    public function __construct(array $rawConfig)
    {
        $this->rawConfig = $rawConfig;
        self::$instance = $this;
    }

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
        return (array)self::getValue($path);
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
        return (bool)self::getValue($path);
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
        return (float)self::getValue($path);
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
        return (int)self::getValue($path);
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
        return (string)self::getValue($path);
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
        if (self::$instance === null) {
            throw new RuntimeException('Must call ' . self::class . '::__construct() before calling get()');
        }

        $explodedPath = \explode('.', $path);
        $value = self::$instance->rawConfig;

        foreach ($explodedPath as $i => $pathPart) {
            if (!isset($value[$pathPart])) {
                $fullPathToThisPart = implode('.', \array_slice($explodedPath, 0, $i + 1));

                throw new ConfigurationException("No configuration value at $fullPathToThisPart");
            }

            $value = $value[$pathPart];
        }

        return $value;
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
        try {
            $value = self::getArray($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
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
        try {
            $value = self::getBool($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
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
        try {
            $value = self::getFloat($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
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
        try {
            $value = self::getInt($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
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
        try {
            $value = self::getString($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
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
        try {
            $value = self::getValue($path);

            return true;
        } catch (ConfigurationException $ex) {
            $value = null;

            return false;
        }
    }
}
