<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers;

use Aphiria\Application\Discoverers\DiscoveredComponentBuilder;
use PHPUnit\Framework\TestCase;

class DiscoveredComponentBuilderTest extends TestCase
{
    public function testFoo(): void
    {
        $componentBuilder = new DiscoveredComponentBuilder(__DIR__ . '/Delete');
        $componentBuilder->build();
        $this->assertTrue(true);
    }
}
