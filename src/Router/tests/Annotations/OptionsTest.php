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

use Aphiria\Routing\Annotations\Options;
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
