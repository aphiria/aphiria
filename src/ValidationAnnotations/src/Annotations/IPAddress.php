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

use Aphiria\Validation\Constraints\IPAddressConstraint;
use Doctrine\Annotations\Annotation\Target;

/**
 * Defines the IP address constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class IPAddress implements IValidationConstraintAnnotation
{
    /** @var string The error message ID */
    public string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        $this->errorMessageId = $values['errorMessageId'] ?? '';
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): IPAddressConstraint
    {
        return new IPAddressConstraint($this->errorMessageId);
    }
}
