<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests\Attributes;

use Aphiria\Authorization\Attributes\AuthorizePolicy;
use PHPUnit\Framework\TestCase;

class AuthorizePolicyTest extends TestCase
{
    public function testPolicyNameParameterIsAutomaticallySet(): void
    {
        $attribute = new AuthorizePolicy('foo');
        $this->assertSame('foo', $attribute->parameters['policyName']);
    }
}
