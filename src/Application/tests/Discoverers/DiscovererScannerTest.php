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

use Aphiria\Application\Discoverers\DiscovererScanner;
use PHPUnit\Framework\TestCase;

class DiscovererScannerTest extends TestCase
{
    public function testFoo(): void
    {
        $scanner = new DiscovererScanner(__DIR__ . '/Delete');
        $scanner->scan();
        $this->assertTrue(true);
    }
}
