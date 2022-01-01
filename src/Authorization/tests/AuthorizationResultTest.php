<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests;

use Aphiria\Authorization\AuthorizationResult;
use PHPUnit\Framework\TestCase;

class AuthorizationResultTest extends TestCase
{
    public function testFailSetsFailedRequirementsAndPassedToFalse(): void
    {
        $result = AuthorizationResult::fail([$this]);
        $this->assertFalse($result->passed);
        $this->assertSame([$this], $result->failedRequirements);
    }

    public function testPassSetsPassedToTrue(): void
    {
        $result = AuthorizationResult::pass();
        $this->assertTrue($result->passed);
        $this->assertEmpty($result->failedRequirements);
    }

    public function testPropertiesSetInConstructor(): void
    {
        $result = new AuthorizationResult(false, [$this]);
        $this->assertFalse($result->passed);
        $this->assertSame([$this], $result->failedRequirements);
    }
}
