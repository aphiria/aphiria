<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\InstanceContainerBinding;
use PHPUnit\Framework\TestCase;
use stdClass;

class InstanceContainerBindingTest extends TestCase
{
    public function testAlwaysResolvedAsSingleton(): void
    {
        $binding = new InstanceContainerBinding(new stdClass());
        $this->assertTrue($binding->resolveAsSingleton());
    }

    public function testCorrectInstanceIsReturned(): void
    {
        $instance = new stdClass();
        $binding = new InstanceContainerBinding($instance);
        $this->assertSame($instance, $binding->instance);
    }
}
