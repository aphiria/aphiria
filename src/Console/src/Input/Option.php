<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

use InvalidArgumentException;

/**
 * Defines a console command option
 */
final class Option
{
    /** @var string The name of the option */
    public string $name;
    /** @var string|null The short name of the option if it has one, otherwise null */
    public ?string $shortName;
    /** @var int The type of option this is */
    public int $type;
    /** @var string|null A brief description of the option */
    public ?string $description;
    /** @var mixed The default value for the option if it's optional */
    public $defaultValue;

    /**
     * @param string $name The name of the option
     * @param string|null $shortName The short name of the option if it has one, otherwise null
     * @param int $type The type of option this is
     * @param string|null $description A brief description of the option
     * @param mixed $defaultValue The default value for the option if it's optional
     * @throws InvalidArgumentException Thrown if the type is invalid
     */
    public function __construct(string $name, ?string $shortName, int $type, string $description = null, mixed $defaultValue = null)
    {
        if (($type & 3) === 3) {
            throw new InvalidArgumentException('Option type cannot be both optional and required');
        }

        if (($type & 5) === 5 || ($type & 6) === 6) {
            throw new InvalidArgumentException('Option cannot have a value and not have a value');
        }

        if ($shortName !== null) {
            if (mb_strlen($shortName) !== 1) {
                throw new InvalidArgumentException('Short names must be one character in length');
            }

            if (!ctype_alpha($shortName)) {
                throw new InvalidArgumentException('Short names must be an alphabet character');
            }
        }

        $this->name = $name;
        $this->shortName = $shortName;
        $this->type = $type;
        $this->description = $description;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Gets whether or not the option value is an array
     *
     * @return bool True if the option value is an array, otherwise false
     */
    public function valueIsArray(): bool
    {
        return ($this->type & OptionTypes::IS_ARRAY) === OptionTypes::IS_ARRAY;
    }

    /**
     * Gets whether or not the option value is optional
     *
     * @return bool True if the option value is optional, otherwise false
     */
    public function valueIsOptional(): bool
    {
        return ($this->type & OptionTypes::OPTIONAL_VALUE) === OptionTypes::OPTIONAL_VALUE;
    }

    /**
     * Gets whether or not the option value is allowed
     *
     * @return bool True if the option value is allowed, otherwise false
     */
    public function valueIsPermitted(): bool
    {
        return ($this->type & OptionTypes::NO_VALUE) !== OptionTypes::NO_VALUE;
    }

    /**
     * Gets whether or not the option value is required
     *
     * @return bool True if the option value is required, otherwise false
     */
    public function valueIsRequired(): bool
    {
        return ($this->type & OptionTypes::REQUIRED_VALUE) === OptionTypes::REQUIRED_VALUE;
    }
}
