<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Tests\Handlers;

use Aphiria\Sessions\Handlers\DriverSessionHandler;
use Aphiria\Sessions\Handlers\ISessionDriver;
use Aphiria\Sessions\Handlers\ISessionEncrypter;
use Aphiria\Sessions\Handlers\SessionEncryptionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the session handler powered by a driver
 */
class DriverSessionHandlerTest extends TestCase
{
    /** @var ISessionDriver|MockObject */
    private ISessionDriver $driver;
    private DriverSessionHandler $sessionHandler;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(ISessionDriver::class);
        $this->sessionHandler = new DriverSessionHandler($this->driver);
    }

    public function testCloseAlwaysReturnsTrue(): void
    {
        $this->assertTrue($this->sessionHandler->close());
    }

    public function testGcCallsGcOnDriver(): void
    {
        $this->driver->expects($this->once())
            ->method('gc')
            ->with(123);
        $this->assertTrue($this->sessionHandler->gc(123));
    }

    public function testOpenAlwaysReturnsTrue(): void
    {
        $this->assertTrue($this->sessionHandler->open('foo', 'bar'));
    }

    public function testReadingWithEncrypterDecryptsValueReturnedByDriver(): void
    {
        /** @var ISessionEncrypter|MockObject $encrypter */
        $encrypter = $this->createMock(ISessionEncrypter::class);
        $sessionHandlerWithEncrypter = new DriverSessionHandler($this->driver, $encrypter);
        $this->driver->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn('bar');
        $encrypter->method('decrypt')
            ->with('bar')
            ->willReturn('baz');
        $this->assertEquals('baz', $sessionHandlerWithEncrypter->read('foo'));
    }

    public function testReadingWithEncrypterThatThrowsExceptionReturnsEmptyString(): void
    {
        /** @var ISessionEncrypter|MockObject $encrypter */
        $encrypter = $this->createMock(ISessionEncrypter::class);
        $sessionHandlerWithEncrypter = new DriverSessionHandler($this->driver, $encrypter);
        $this->driver->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn('bar');
        $encrypter->method('decrypt')
            ->with('bar')
            ->willThrowException(new SessionEncryptionException());
        $this->assertEquals('', $sessionHandlerWithEncrypter->read('foo'));
    }

    public function testReadingWithoutEncrypterPassesThroughDriverValue(): void
    {
        $this->driver->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn('bar');
        $this->assertEquals('bar', $this->sessionHandler->read('foo'));
    }

    public function testWritingWithEncrypterEncryptsValueBeforeSettingItInDriver(): void
    {
        /** @var ISessionEncrypter|MockObject $encrypter */
        $encrypter = $this->createMock(ISessionEncrypter::class);
        $sessionHandlerWithEncrypter = new DriverSessionHandler($this->driver, $encrypter);
        $this->driver->expects($this->once())
            ->method('set')
            ->with('foo', 'baz');
        $encrypter->method('encrypt')
            ->with('bar')
            ->willReturn('baz');
        $this->assertTrue($sessionHandlerWithEncrypter->write('foo', 'bar'));
    }

    public function testWritingWithEncrypterThatThrowsExceptionReturnsFalse(): void
    {
        /** @var ISessionEncrypter|MockObject $encrypter */
        $encrypter = $this->createMock(ISessionEncrypter::class);
        $sessionHandlerWithEncrypter = new DriverSessionHandler($this->driver, $encrypter);
        $this->driver->expects($this->never())
            ->method('set');
        $encrypter->method('encrypt')
            ->with('bar')
            ->willThrowException(new SessionEncryptionException());
        $this->assertFalse($sessionHandlerWithEncrypter->write('foo', 'bar'));
    }

    public function testWritingWithoutEncrypterPassesValueThroughToDriver(): void
    {
        $this->driver->expects($this->once())
            ->method('set')
            ->with('foo', 'bar');
        $this->assertTrue($this->sessionHandler->write('foo', 'bar'));
    }
}
