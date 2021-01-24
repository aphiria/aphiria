<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Errors;

use Aphiria\Api\Errors\ProblemDetails;
use PHPUnit\Framework\TestCase;

class ProblemDetailsTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $problemDetails = new ProblemDetails('type', 'title', 'detail', 1, 'instance', ['foo' => 'bar']);
        $this->assertSame('type', $problemDetails->type);
        $this->assertSame('title', $problemDetails->title);
        $this->assertSame('detail', $problemDetails->detail);
        $this->assertSame(1, $problemDetails->status);
        $this->assertSame('instance', $problemDetails->instance);
        $this->assertSame(['foo' => 'bar'], $problemDetails->extensions);
    }
}
