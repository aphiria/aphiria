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

use Aphiria\Validation\Constraints\RegexConstraint;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Defines the regex constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Regex implements IConstraintAnnotation
{
    /** @var string The regex to apply */
    public string $regex;
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (!isset($values['value']) || !\is_string($values['value'])) {
            throw new \InvalidArgumentException('Regex must be set');
        }

        $this->regex = $values['value'];
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): RegexConstraint
    {
        if (isset($this->errorMessageId)) {
            return new RegexConstraint($this->regex, $this->errorMessageId);
        }

        return new RegexConstraint($this->regex);
    }
}
