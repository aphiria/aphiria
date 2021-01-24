<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Mocks;

use Aphiria\DependencyInjection\Binders\Binder as BaseBinder;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines a mocked binder
 */
class Binder extends BaseBinder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        // Don't do anything
    }
}
