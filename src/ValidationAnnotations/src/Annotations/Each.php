<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ValidationAnnotations\Annotations;

use Aphiria\Validation\Constraints\EachConstraint;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the each constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Each implements IValidationConstraintAnnotation
{
    /** @var IValidationConstraintAnnotation[] The list of constraint annotations to apply on each value */
    public array $constraints;
    /** @var string The error message ID */
    public string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     * @throws InvalidArgumentException Thrown if no list of constraints was passed in
     */
    public function __construct(array $values)
    {
        if (!isset($values['value'])) {
            throw new InvalidArgumentException('Must specify a constraint');
        }

        if (\is_array($values['value'])) {
            if (count($values['value']) === 0) {
                throw new InvalidArgumentException('Must specify at least one constraint');
            }

            $this->constraints = $values['value'];
        } else {
            $this->constraints = [$values['value']];
        }

        $this->errorMessageId = $values['errorMessageId'] ?? '';
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): EachConstraint
    {
        $constraints = [];

        foreach ($this->constraints as $constraintAnnotations) {
            $constraints[] = $constraintAnnotations->createConstraintFromAnnotation();
        }

        return new EachConstraint($constraints, $this->errorMessageId);
    }
}
