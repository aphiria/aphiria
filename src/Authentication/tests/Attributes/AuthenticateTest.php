<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests\Attributes;

use Aphiria\Authentication\Attributes\Authenticate;
use PHPUnit\Framework\TestCase;

class AuthenticateTest extends TestCase
{
    public function testMultipleSchemeNamesParametersAreSet(): void
    {
        $attribute = new Authenticate(['foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $attribute->parameters['schemeNames']);
    }

    public function testSingleSchemeNameParameterIsSet(): void
    {
        $attribute = new Authenticate('foo');
        $this->assertSame(['foo'], $attribute->parameters['schemeNames']);
    }
}
