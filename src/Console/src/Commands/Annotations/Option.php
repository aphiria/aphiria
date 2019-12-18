<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Annotations;

use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the annotation for command options
 * @Annotation
 * @Target({"ANNOTATION"})
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
     * @param array $values The mapping of value names to values
     * @throws InvalidArgumentException Thrown if required values were not set
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->name = $values['value'];
            unset($values['value']);
        }

        if (isset($values['name'])) {
            $this->name = $values['name'];
        }

        if (empty($this->name)) {
            throw new InvalidArgumentException('Argument name must be set');
        }

        if (!isset($values['type'])) {
            throw new InvalidArgumentException('Argument type must be set');
        }

        $this->shortName = $values['shortName'] ?? null;
        $this->type = $values['type'] ?? -1;
        $this->description = $values['description'] ?? null;
        $this->defaultValue = $values['defaultValue'] ?? null;
    }
}
