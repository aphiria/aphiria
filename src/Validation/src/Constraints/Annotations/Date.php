<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Annotations;

use Aphiria\Validation\Constraints\DateConstraint;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the date constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Date implements IConstraintAnnotation
{
    /** @var string[] The list of acceptable date formats */
    public array $acceptableFormats;
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     * @throws InvalidArgumentException Thrown if no acceptable date formats were specified
     */
    public function __construct(array $values)
    {
        if (!isset($values['value'])) {
            throw new InvalidArgumentException('Must specify an acceptable date format');
        }

        if (\is_array($values['value'])) {
            if (\count($values['value']) === 0) {
                throw new InvalidArgumentException('Must specify at least one acceptable date format');
            }

            $this->acceptableFormats = $values['value'];
        } else {
            $this->acceptableFormats = [$values['value']];
        }

        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): DateConstraint
    {
        if (isset($this->errorMessageId)) {
            return new DateConstraint($this->acceptableFormats, $this->errorMessageId);
        }

        return new DateConstraint($this->acceptableFormats);
    }
}
