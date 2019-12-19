<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Closure;

/**
 * Defines the constraint registrant that takes in a list of closures that perform the registration
 */
final class ClosureObjectConstraintRegistrant implements IObjectConstraintRegistrant
{
    /** @var Closure[] The list of closures to execute */
    private array $closures;

    /**
     * @param Closure[] $closures The list of closures to execute
     */
    public function __construct(array $closures)
    {
        $this->closures = $closures;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
    {
        foreach ($this->closures as $closure) {
            $closure($objectConstraints);
        }
    }
}
