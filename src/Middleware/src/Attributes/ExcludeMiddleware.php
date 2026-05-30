<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Attributes;

use Aphiria\Middleware\IMiddleware;
use Attribute;
use InvalidArgumentException;

/**
 * Defines the exclude middleware attribute
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ExcludeMiddleware
{
    /**
     * @param class-string<IMiddleware> $className The name of the middleware class to exclude from bindings
     * @throws InvalidArgumentException Thrown if the class name is empty
     */
    public function __construct(public readonly string $className)
    {
        /** @psalm-suppress DocblockTypeContradiction We want runtime reassurance that this is never empty */
        if (empty($this->className)) {
            throw new InvalidArgumentException('Class name must be set');
        }
    }
}
