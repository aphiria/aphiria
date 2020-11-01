<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Attributes;

use Attribute;

/**
 * Defines the attribute for command arguments
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Argument
{
    /**
     * @param string $name The name of the argument
     * @param int $type The type of argument this is
     * @param string|null $description A brief description of the argument, or null if there is none
     * @param mixed|null $defaultValue The default value for the argument if it's optional
     */
    public function __construct(
        public string $name,
        public int $type,
        public ?string $description = null,
        public mixed $defaultValue = null
    ) {
    }
}
