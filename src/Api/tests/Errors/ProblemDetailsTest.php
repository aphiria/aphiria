<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Errors;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Net\Http\HttpStatusCode;
use PHPUnit\Framework\TestCase;

class ProblemDetailsTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $problemDetails = new ProblemDetails('type', 'title', 'detail', 200, 'instance', ['foo' => 'bar']);
        $this->assertSame('type', $problemDetails->type);
        $this->assertSame('title', $problemDetails->title);
        $this->assertSame('detail', $problemDetails->detail);
        $this->assertSame(200, $problemDetails->status);
        $this->assertSame('instance', $problemDetails->instance);
        $this->assertSame(['foo' => 'bar'], $problemDetails->extensions);
    }

    public function testEnumStatusCodeGetsConverted(): void
    {
        $problemDetails = new ProblemDetails(status: HttpStatusCode::Created);
        $this->assertSame(201, $problemDetails->status);
    }
}
