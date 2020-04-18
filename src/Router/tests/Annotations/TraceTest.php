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

use Aphiria\Routing\Annotations\Trace;
use PHPUnit\Framework\TestCase;

class TraceTest extends TestCase
{
    public function testTraceHttpMethodIsSet(): void
    {
        $this->assertEquals(['TRACE'], (new Trace([]))->httpMethods);
    }
}
