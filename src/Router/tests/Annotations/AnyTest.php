<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Routing\Annotations\Any;
use PHPUnit\Framework\TestCase;

/**
 * Tests the annotation for any HTTP method
 */
class AnyTest extends TestCase
{
    public function testNoHttpMethodsSet(): void
    {
        $this->assertEmpty((new Any([]))->httpMethods);
    }
}
