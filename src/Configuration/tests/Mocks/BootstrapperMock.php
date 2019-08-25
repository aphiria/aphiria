<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests\Mocks;

use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines a mock bootstrapper for use in testing
 */
final class BootstrapperMock extends Bootstrapper
{
    public function registerBindings(IContainer $container): void
    {
        // Don't do anything
    }
}
