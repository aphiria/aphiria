<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests;

use Aphiria\Exceptions\GlobalExceptionHandler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the global exception handler
 */
class GlobalExceptionHandlerTest extends TestCase
{
    public function testDummy(): void
    {
        $this->assertTrue(true);
    }
}
