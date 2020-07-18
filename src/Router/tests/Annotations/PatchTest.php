<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Routing\Annotations\Patch;
use PHPUnit\Framework\TestCase;

class PatchTest extends TestCase
{
    public function testPatchHttpMethodIsSet(): void
    {
        $this->assertEquals(['PATCH'], (new Patch([]))->httpMethods);
    }
}
