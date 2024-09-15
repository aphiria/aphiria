<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\Collections\HashTable;
use Aphiria\Net\Http\IRequest;
use Aphiria\Security\IPrincipal;
use PHPUnit\Framework\TestCase;

class RequestPropertyUserAccessorTest extends TestCase
{
    private RequestPropertyUserAccessor $userAccessor;

    protected function setUp(): void
    {
        $this->userAccessor = new RequestPropertyUserAccessor();
    }

    public function testGetUserWhenNoneIsRegisteredReturnsNull(): void
    {
        $request = $this->createMock(IRequest::class);
        $properties = new HashTable();
        $request->method('$properties::get')
            ->willReturn($properties);
        $this->assertNull($this->userAccessor->getUser($request));
    }

    public function testGetUserWhenOneIsRegisteredReturnsIt(): void
    {
        $request = $this->createMock(IRequest::class);
        $properties = new HashTable();
        $request->method('$properties::get')
            ->willReturn($properties);
        $expectedUser = $this->createMock(IPrincipal::class);
        $this->userAccessor->setUser($expectedUser, $request);
        $this->assertSame($expectedUser, $this->userAccessor->getUser($request));
    }
}
