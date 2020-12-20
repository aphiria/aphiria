<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Tests;

use Aphiria\Sessions\Ids\IIdGenerator;
use Aphiria\Sessions\Session;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testAgingFlashDataEvictsOldData(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $this->assertEquals(
            [
                'foo' => 'bar',
                $session::NEW_FLASH_KEYS_KEY => ['foo'],
                $session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->getAll()
        );
        $session->ageFlashData();
        $this->assertSame('bar', $session->get('foo'));
        $this->assertTrue($session->containsKey('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                $session::NEW_FLASH_KEYS_KEY => [],
                $session::STALE_FLASH_KEYS_KEY => ['foo']
            ],
            $session->getAll()
        );
        $session->flash('baz', 'blah');
        $session->ageFlashData();
        $this->assertNull($session->get('foo'));
        $this->assertFalse($session->containsKey('foo'));
        $this->assertSame('blah', $session->get('baz'));
        $this->assertEquals(
            [
                'baz' => 'blah',
                $session::NEW_FLASH_KEYS_KEY => [],
                $session::STALE_FLASH_KEYS_KEY => ['baz']
            ],
            $session->getAll()
        );
        $this->assertTrue($session->containsKey('baz'));
        $session->ageFlashData();
        $this->assertNull($session->get('baz'));
        $this->assertFalse($session->containsKey('baz'));
        $this->assertEquals(
            [
                $session::NEW_FLASH_KEYS_KEY => [],
                $session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->getAll()
        );
    }

    public function testAgingFlashDataAndWritingToItAgainKeepsItInSession(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $session->ageFlashData();
        $session->flash('foo', 'baz');
        $session->ageFlashData();
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('baz', $session->get('foo'));
        $this->assertEquals(
            [
                'foo' => 'baz',
                $session::NEW_FLASH_KEYS_KEY => [],
                $session::STALE_FLASH_KEYS_KEY => ['foo']
            ],
            $session->getAll()
        );
        $session->ageFlashData();
        $this->assertFalse($session->containsKey('foo'));
        $this->assertNull($session->get('foo'));
        $this->assertEquals(
            [
                $session::NEW_FLASH_KEYS_KEY => [],
                $session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->getAll()
        );
    }

    public function testCheckingIfOffsetExists(): void
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertTrue(isset($session['foo']));
        $this->assertTrue($session->containsKey('foo'));
        $this->assertFalse(isset($session['bar']));
        $this->assertFalse($session->containsKey('bar'));
    }

    public function testDeletingKeyRemovesItFromSession(): void
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $session->delete('foo');
        $this->assertFalse($session->containsKey('foo'));
        $this->assertEquals([], $session->getAll());
    }

    public function testFlashingDataAndGettingItMakesItStillAccessibleOneMoreTime(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('bar', $session->get('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                $session::NEW_FLASH_KEYS_KEY => ['foo'],
                $session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->getAll()
        );
    }

    public function testFlushingRemovesAllDataFromSession(): void
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $session->flush();
        $this->assertFalse(isset($session['foo']));
        $this->assertEquals([], $session->getAll());
    }

    public function testGettingAllReturnsAllSetKeys(): void
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $session->set('baz', 'blah');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $session->getAll());
    }

    public function testGettingIdReturnsSameOneAsSet(): void
    {
        $constructorId = str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')->willReturn(true);
        $session = new Session($constructorId, $idGenerator);
        $setterId = str_repeat('2', IIdGenerator::MIN_LENGTH);
        $session->setId($setterId);
        $this->assertSame($setterId, $session->getId());
    }

    public function testGettingIdUsesIdGeneratorValue(): void
    {
        $id = str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')->willReturn(true);
        $session = new Session($id, $idGenerator);
        $this->assertSame($id, $session->getId());
    }

    public function testGettingNonExistentKeyReturnsNull(): void
    {
        $session = new Session();
        $this->assertNull($session['non-existent']);
        $this->assertNull($session->get('non-existent'));
    }

    public function testGettingNonExistentKeyWithDefaultValueReturnsThatValue(): void
    {
        $session = new Session();
        $this->assertSame('bar', $session->get('foo', 'bar'));
    }

    public function testReflashingKeepsTheDataInSessionUntilItIsAgedAgain(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $session->ageFlashData();
        $session->reflash();
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('bar', $session->get('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                $session::NEW_FLASH_KEYS_KEY => ['foo'],
                $session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->getAll()
        );
        $session->ageFlashData();
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('bar', $session->get('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                $session::NEW_FLASH_KEYS_KEY => [],
                $session::STALE_FLASH_KEYS_KEY => ['foo']
            ],
            $session->getAll()
        );
        $session->ageFlashData();
        $this->assertFalse($session->containsKey('foo'));
        $this->assertNull($session->get('foo'));
        $this->assertEquals(
            [
                $session::NEW_FLASH_KEYS_KEY => [],
                $session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->getAll()
        );
    }

    public function testRegenerateIdUsesIdGeneratorValue(): void
    {
        $generatedId = str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')
            ->willReturnMap([
                [null, false],
                [$generatedId, true]
            ]);
        $idGenerator->method('generate')->willReturn($generatedId);
        $session = new Session(null, $idGenerator);
        $session->regenerateId();
        $this->assertSame($generatedId, $session->getId());
    }

    public function testRegeneratingIdWithDefaultIdGeneratorCreatesANewId(): void
    {
        $session = new Session();
        $session->regenerateId();
        $this->assertIsString($session->getId());
    }

    public function testSettingInvalidIdCausesNewIdToBeGenerated(): void
    {
        $generatedId = str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')
            ->willReturnMap([
                [1, false],
                [2, false],
                [$generatedId, true]
            ]);
        $idGenerator->method('generate')->willReturn($generatedId);
        $session = new Session(1, $idGenerator);
        $this->assertNotEquals(1, $session->getId());
        $session->setId(2);
        $this->assertNotEquals(2, $session->getId());
    }

    public function testSettingManyWritesAllValuesToSession(): void
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $session->setMany(['baz' => 'blah']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $session->getAll());
        $session->setMany(['foo' => 'somethingnew']);
        $this->assertEquals(['foo' => 'somethingnew', 'baz' => 'blah'], $session->getAll());
    }

    public function testSettingNullOffsetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $session = new Session();
        $session[] = 'foo';
    }

    public function testSettingOffsetWritesKeysToSession(): void
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertSame('bar', $session['foo']);
        $this->assertSame('bar', $session->get('foo'));
        $this->assertEquals(['foo' => 'bar'], $session->getAll());
    }

    public function testSettingKeyWritesItToSession(): void
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $this->assertSame('bar', $session->get('foo'));
        $this->assertEquals(['foo' => 'bar'], $session->getAll());
    }

    public function testUnsettingOffsetRemovesTheKeyFromSession(): void
    {
        $session = new Session();
        $session['foo'] = 'bar';
        unset($session['foo']);
        $this->assertNull($session['foo']);
        $this->assertEquals([], $session->getAll());
    }
}
