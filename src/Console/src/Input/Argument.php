<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

use InvalidArgumentException;

/**
 * Defines a console command argument
 */
final class Argument
{
    /** @var list<ArgumentType> The type of argument this is */
    public readonly array $type;
    /** @var int The bitwise-OR'd flag representing all the types */
    private int $typeFlag = 0;

    /**
     * @param string $name The name of the argument
     * @param list<ArgumentType>|ArgumentType $type The type of argument this is
     * @param string|null $description A brief description of the argument
     * @param mixed $defaultValue The default value for the argument if it's optional
     * @throws InvalidArgumentException Thrown if the type is invalid
     */
    public function __construct(
        public readonly string $name,
        array|ArgumentType $type,
        public readonly ?string $description = null,
        public readonly mixed $defaultValue = null
    ) {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Argument name cannot be empty');
        }

        $this->type = \is_array($type) ? $type : [$type];

        foreach ($this->type as $type) {
            $this->typeFlag |= $type->value;
        }

        if (($this->typeFlag & 3) === 3) {
            throw new InvalidArgumentException('Argument type cannot be both optional and required');
        }
    }

    /**
     * Gets whether or not the argument is an array
     *
     * @return bool True if the argument is an array, otherwise false
     */
    public function isArray(): bool
    {
        return ($this->typeFlag & ArgumentType::IsArray->value) === ArgumentType::IsArray->value;
    }

    /**
     * Gets whether or not the argument is optional
     *
     * @return bool True if the argument is optional, otherwise false
     */
    public function isOptional(): bool
    {
        return ($this->typeFlag & ArgumentType::Optional->value) === ArgumentType::Optional->value;
    }

    /**
     * Gets whether or not the argument is required
     *
     * @return bool True if the argument is required, otherwise false
     */
    public function isRequired(): bool
    {
        return ($this->typeFlag & ArgumentType::Required->value) === ArgumentType::Required->value;
    }
}
