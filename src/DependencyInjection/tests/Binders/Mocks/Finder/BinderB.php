<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Mocks\Finder;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines a mock binder
 */
class BinderB extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        // Don't do anything
    }
}
