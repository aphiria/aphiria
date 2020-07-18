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

use Aphiria\Routing\Annotations\Post;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testPostHttpMethodIsSet(): void
    {
        $this->assertEquals(['POST'], (new Post([]))->httpMethods);
    }
}
