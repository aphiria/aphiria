<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleAnnotations\Annotations;

use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the command annotation
 * @Annotation
 * @Target({"CLASS"})
 */
final class Command
{
    /** @var string The name of the command */
    public string $name;
    /** @var Argument[] The list of arguments */
    public array $arguments;
    /** @var Option[] The list of options */
    public array $options;
    /** @var string|null The description of the command */
    public ?string $description;
    /** @var string|null The extra descriptive help text */
    public ?string $helpText;

    /**
     * @param array $values The mapping of value names to values
     * @throws InvalidArgumentException Thrown if required values are not set
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
            throw new InvalidArgumentException('Command name must be set');
        }

        $this->arguments = $values['arguments'] ?? [];
        $this->options = $values['options'] ?? [];
        $this->description = $values['description'] ?? null;
        $this->helpText = $values['helpText'] ?? null;
    }
}
