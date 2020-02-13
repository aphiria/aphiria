<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Errors;

use Aphiria\Api\Errors\ProblemDetails;
use PHPUnit\Framework\TestCase;

/**
 * Tests the problem details
 */
class ProblemDetailsTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $problemDetails = new ProblemDetails('type', 'title', 'detail', 1, 'instance');
        $this->assertEquals('type', $problemDetails->type);
        $this->assertEquals('title', $problemDetails->title);
        $this->assertEquals('detail', $problemDetails->detail);
        $this->assertEquals(1, $problemDetails->status);
        $this->assertEquals('instance', $problemDetails->instance);
    }
}
