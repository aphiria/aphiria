<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class UniversalContextTest extends TestCase
{
    public function testMethodsIndicateUniversalContext(): void
    {
        $context = new UniversalContext();
        $this->assertNull($context->getTargetClass());
        $this->assertFalse($context->isTargeted());
        $this->assertTrue($context->isUniversal());
    }
}
