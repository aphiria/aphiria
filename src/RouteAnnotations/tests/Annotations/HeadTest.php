<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Tests\Annotations;

use Aphiria\RouteAnnotations\Annotations\Head;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HEAD annotation
 */
class HeadTest extends TestCase
{
    public function testHeadHttpMethodIsSet(): void
    {
        $this->assertEquals(['HEAD'], (new Head([]))->httpMethods);
    }
}
