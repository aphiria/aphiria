<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
        $this->assertEquals('type', $problemDetails->type);
        $this->assertEquals('title', $problemDetails->title);
        $this->assertEquals('detail', $problemDetails->detail);
        $this->assertEquals(1, $problemDetails->status);
        $this->assertEquals('instance', $problemDetails->instance);
    }
}
