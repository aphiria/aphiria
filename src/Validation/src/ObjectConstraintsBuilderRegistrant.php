<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Aphiria\Validation\Constraints\IObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Closure;

/**
 * Defines the constraint registrant that populates the object constraints using builders
 */
final class ObjectConstraintsBuilderRegistrant implements IObjectConstraintsRegistrant
{
    /**
     * @param array<Closure(ObjectConstraintsRegistryBuilder): void> $closures The list of closures to execute (must take in an ObjectConstraintsRegistryBuilder parameter)
     */
    public function __construct(private array $closures)
    {
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
    {
        $objectConstraintsRegistryBuilder = new ObjectConstraintsRegistryBuilder($objectConstraints);

        foreach ($this->closures as $closure) {
            $closure($objectConstraintsRegistryBuilder);
        }

        $objectConstraintsRegistryBuilder->build();
    }
}
