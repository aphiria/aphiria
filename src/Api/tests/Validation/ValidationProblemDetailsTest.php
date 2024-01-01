<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Validation;

use Aphiria\Api\Validation\ValidationProblemDetails;
use PHPUnit\Framework\TestCase;

class ValidationProblemDetailsTest extends TestCase
{
    public function testConstructorSetsAllTheProperties(): void
    {
        $problemDetails = new ValidationProblemDetails(['error'], 'type', 'title', 'detail', 1, 'instance');
        $this->assertEquals(['error'], $problemDetails->errors);
        $this->assertSame('type', $problemDetails->type);
        $this->assertSame('title', $problemDetails->title);
        $this->assertSame('detail', $problemDetails->detail);
        $this->assertSame(1, $problemDetails->status);
        $this->assertSame('instance', $problemDetails->instance);
    }
}
