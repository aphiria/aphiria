<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\ErrorMessages;

use Aphiria\Validation\ErrorMessages\DefaultErrorMessageTemplateRegistry;
use PHPUnit\Framework\TestCase;

class DefaultErrorMessageTemplateRegistryTest extends TestCase
{
    public function testGetErrorMessageTemplateReturnsErrorMessageIdRegardlessOfLocale(): void
    {
        $errorMessageTemplates = new DefaultErrorMessageTemplateRegistry();
        $this->assertSame('foo', $errorMessageTemplates->getErrorMessageTemplate('foo'));
        $this->assertSame('foo', $errorMessageTemplates->getErrorMessageTemplate('foo', 'de'));
    }
}
