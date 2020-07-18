<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata\Caching\Mocks;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Binder;

/**
 * Mocks a binder for use in testing
 */
final class MockBinder extends Binder
{
    public function bind(IContainer $container): void
    {
        // Don't do anything
    }
}
