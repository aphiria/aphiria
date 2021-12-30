<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests\Schemes;

use Aphiria\Authentication\Schemes\BasicAuthenticationOptions;
use PHPUnit\Framework\TestCase;

class BasicAuthenticationOptionsTest extends TestCase
{
    public function testConstructorSetsRealm(): void
    {
        $options = new BasicAuthenticationOptions('foo');
        $this->assertSame('foo', $options->realm);
    }
}
