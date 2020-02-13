<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Bootstrappers\Inspection\Caching\Mocks;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Bootstrappers\Mocks\Bootstrapper;

/**
 * Mocks a bootstrapper for use in testing
 */
final class MockBootstrapper extends Bootstrapper
{
    public function registerBindings(IContainer $container): void
    {
        // Don't do anything
    }
}
