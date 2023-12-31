<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests\Schemes;

use Aphiria\Authentication\Schemes\CookieAuthenticationOptions;
use Aphiria\Net\Http\Headers\SameSiteMode;
use PHPUnit\Framework\TestCase;

class CookieAuthenticationOptionsTest extends TestCase
{
    public function testPropertiesSetInConstructor(): void
    {
        $options = new CookieAuthenticationOptions(
            cookieName: 'cookie',
            cookieMaxAge: 360,
            cookiePath: '/path',
            cookieDomain: 'example.com',
            cookieIsSecure: true,
            cookieIsHttpOnly: true,
            cookieSameSite: SameSiteMode::Strict,
            loginPagePath: '/login',
            forbiddenPagePath: '/forbidden',
            claimsIssuer: 'issuer'
        );
        $this->assertSame('cookie', $options->cookieName);
        $this->assertSame(360, $options->cookieMaxAge);
        $this->assertSame('/path', $options->cookiePath);
        $this->assertSame('example.com', $options->cookieDomain);
        $this->assertTrue($options->cookieIsSecure);
        $this->assertTrue($options->cookieIsHttpOnly);
        $this->assertSame(SameSiteMode::Strict, $options->cookieSameSite);
        $this->assertSame('/login', $options->loginPagePath);
        $this->assertSame('/forbidden', $options->forbiddenPagePath);
        $this->assertSame('issuer', $options->claimsIssuer);
    }
}
