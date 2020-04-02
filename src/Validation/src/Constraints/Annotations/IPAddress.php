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

use Aphiria\Validation\Constraints\IPAddressConstraint;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Defines the IP address constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class IPAddress implements IConstraintAnnotation
{
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): IPAddressConstraint
    {
        if (isset($this->errorMessageId)) {
            return new IPAddressConstraint($this->errorMessageId);
        }

        return new IPAddressConstraint();
    }
}
