<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Builders;

/**
 * Defines the interface for lazy component builders, ie ones that are not instantiated and built until later in the application lifetime
 */
interface IComponentBuilderProxy extends IComponentBuilder
{
    /**
     * Gets the type of the component builder that is wrapped by the lazy instance
     *
     * @return string The type of the underlying lazy component builder
     */
    public function getProxiedType(): string;
}
