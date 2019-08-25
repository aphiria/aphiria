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

use Aphiria\RouteAnnotations\Annotations\Delete;
use PHPUnit\Framework\TestCase;

/**
 * Tests the DELETE annotation
 */
class DeleteTest extends TestCase
{
    public function testDeleteHttpMethodIsSet(): void
    {
        $this->assertEquals(['DELETE'], (new Delete([]))->httpMethods);
    }
}
