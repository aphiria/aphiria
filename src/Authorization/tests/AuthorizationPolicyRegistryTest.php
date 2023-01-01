<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests;

use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class AuthorizationPolicyRegistryTest extends TestCase
{
    private AuthorizationPolicyRegistry $policies;

    protected function setUp(): void
    {
        $this->policies = new AuthorizationPolicyRegistry();
    }

    public function testGettingRegisteredPolicyReturnsIt(): void
    {
        $policy = new AuthorizationPolicy('foo', $this, []);
        $this->policies->registerPolicy($policy);
        $this->assertSame($policy, $this->policies->getPolicy('foo'));
    }

    public function testGettingUnregisteredPolicyThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No policy with name "foo" found');
        $this->policies->getPolicy('foo');
    }
}
