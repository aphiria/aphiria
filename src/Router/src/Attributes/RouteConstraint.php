<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Attribute;
use InvalidArgumentException;

/**
 * Defines a route constraint attribute
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class RouteConstraint
{
    /**
     * @param class-string<IRouteConstraint> $className The name of the constraint class
     * @param list<mixed> $constructorParameters The list of constructor parameters for the constraint class
     * @throws InvalidArgumentException Thrown if any of the parameters are invalid
     */
    public function __construct(public string $className, public array $constructorParameters = [])
    {
        /** @psalm-suppress DocblockTypeContradiction We want runtime reassurance that this is never empty */
        if (empty($this->className)) {
            throw new InvalidArgumentException('Class name must be set');
        }
    }
}
