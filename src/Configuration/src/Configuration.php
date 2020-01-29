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
     * Gets the value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return mixed The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function get(string $path)
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
     * Gets the array value at the path
     *
     * @param string $path The period-delimited path to the value in the config to get
     * @return array The value at the path
     * @throws RuntimeException Thrown if the underlying config was not set first
     * @throws ConfigurationException Thrown if there was no value at the input path
     */
    public static function getArray(string $path): array
    {
        return (array)self::get($path);
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
        return (bool)self::get($path);
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
        return (float)self::get($path);
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
        return (int)self::get($path);
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
        return (string)self::get($path);
    }
}
