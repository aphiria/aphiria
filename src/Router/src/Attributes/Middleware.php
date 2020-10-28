<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;
use InvalidArgumentException;

/**
 * Defines the middleware attribute
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
final class Middleware
{
    /**
     * @param class-string $className The name of the middleware class
     * @param array $parameters The mapping of parameter names to values
     * @throws InvalidArgumentException Thrown if any of the parameters are invalid
     */
    public function __construct(public string $className, public array $parameters = [])
    {
        /** @psalm-suppress DocblockTypeContradiction We want runtime reassurance that this is never empty */
        if (empty($this->className)) {
            throw new InvalidArgumentException('Class name must be set');
        }
    }
}
