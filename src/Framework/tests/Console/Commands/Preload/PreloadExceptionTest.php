<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands\Preload;

use Aphiria\Framework\Console\Commands\Preload\PreloadException;
use Exception;
use PHPUnit\Framework\TestCase;

class PreloadExceptionTest extends TestCase
{
    public function testPreloadExceptionExtendsException(): void
    {
        $exception = new PreloadException('Test message');
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }
}
