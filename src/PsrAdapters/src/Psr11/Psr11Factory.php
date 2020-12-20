<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Psr11;

use Aphiria\DependencyInjection\IContainer;
use Psr\Container\ContainerInterface;

/**
 * Defines a factory for PSR-11 models
 */
class Psr11Factory implements IPsr11Factory
{
    /**
     * @inheritdoc
     */
    public function createPsr11Container(IContainer $container): ContainerInterface
    {
        return new Psr11Container($container);
    }
}
