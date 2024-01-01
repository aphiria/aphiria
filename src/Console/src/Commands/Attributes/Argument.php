<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Attributes;

use Aphiria\Console\Input\ArgumentType;
use Attribute;

/**
 * Defines the attribute for command arguments
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Argument
{
    /** @var list<ArgumentType> The type of argument this is */
    public array $type;

    /**
     * @param string $name The name of the argument
     * @param list<ArgumentType>|ArgumentType $type The type of argument this is
     * @param string|null $description A brief description of the argument, or null if there is none
     * @param mixed|null $defaultValue The default value for the argument if it's optional
     */
    public function __construct(
        public string $name,
        array|ArgumentType $type,
        public ?string $description = null,
        public mixed $defaultValue = null
    ) {
        $this->type = \is_array($type) ? $type : [$type];
    }
}
