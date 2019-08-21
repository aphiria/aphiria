<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Tests\Annotations;

use Aphiria\RouteAnnotations\Annotations\Options;
use PHPUnit\Framework\TestCase;

/**
 * Tests the OPTIONS annotation
 */
class OptionsTest extends TestCase
{
    public function testOptionsHttpMethodIsSet(): void
    {
        $this->assertEquals(['OPTIONS'], (new Options([]))->httpMethods);
    }
}
