<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Routing\Annotations\Put;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PUT annotation
 */
class PutTest extends TestCase
{
    public function testPutHttpMethodIsSet(): void
    {
        $this->assertEquals(['PUT'], (new Put([]))->httpMethods);
    }
}
