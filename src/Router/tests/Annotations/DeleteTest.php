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

use Aphiria\Routing\Annotations\Delete;
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
