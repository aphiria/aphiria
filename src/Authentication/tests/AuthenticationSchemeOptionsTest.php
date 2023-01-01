<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\AuthenticationSchemeOptions;
use PHPUnit\Framework\TestCase;

class AuthenticationSchemeOptionsTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $options = new AuthenticationSchemeOptions('foo');
        $this->assertSame('foo', $options->claimsIssuer);
    }
}
