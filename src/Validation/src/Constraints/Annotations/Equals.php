<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Annotations;

use Aphiria\Validation\Constraints\EqualsConstraint;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the equals constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Equals implements IConstraintAnnotation
{
    /** @var mixed The value to compare against */
    public $value;
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     * @throws InvalidArgumentException Thrown if no value was passed in
     */
    public function __construct(array $values)
    {
        if (!array_key_exists('value', $values)) {
            throw new InvalidArgumentException('Must specify a value to compare against');
        }

        $this->value = $values['value'];
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): EqualsConstraint
    {
        if (isset($this->errorMessageId)) {
            return new EqualsConstraint($this->value, $this->errorMessageId);
        }

        return new EqualsConstraint($this->value);
    }
}
