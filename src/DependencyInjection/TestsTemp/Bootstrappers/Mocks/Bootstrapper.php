<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/dependency-injection/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Bootstrappers\Mocks;

use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper as BaseBootstrapper;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines a mocked bootstrapper
 */
class Bootstrapper extends BaseBootstrapper
{
    /**
     * @inheritdoc
     */
    public function registerBindings(IContainer $container): void
    {
        // Don't do anything
    }
}
