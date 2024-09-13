<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines the interface for container bindings to implement
 * @template T of object
 * @internal
 */
interface IContainerBinding
{
    /** @var bool Whether or not this binding should be resolved as a singleton */
    public bool $resolveAsSingleton { get; }
}
