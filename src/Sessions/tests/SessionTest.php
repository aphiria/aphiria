<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
    public function testAgingFlashDataAndWritingToItAgainKeepsItInSession(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $session->ageFlashData();
        $session->flash('foo', 'baz');
        $session->ageFlashData();
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('baz', $session->getVariable('foo'));
        $this->assertEquals(
            [
                'foo' => 'baz',
                Session::NEW_FLASH_KEYS_KEY => [],
                Session::STALE_FLASH_KEYS_KEY => ['foo']
            ],
            $session->variables
        );
        $session->ageFlashData();
        $this->assertFalse($session->containsKey('foo'));
        $this->assertNull($session->getVariable('foo'));
        $this->assertEquals(
            [
                Session::NEW_FLASH_KEYS_KEY => [],
                Session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->variables
        );
    }
    public function testAgingFlashDataEvictsOldData(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $this->assertEquals(
            [
                'foo' => 'bar',
                Session::NEW_FLASH_KEYS_KEY => ['foo'],
                Session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->variables
        );
        $session->ageFlashData();
        $this->assertSame('bar', $session->getVariable('foo'));
        $this->assertTrue($session->containsKey('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                Session::NEW_FLASH_KEYS_KEY => [],
                Session::STALE_FLASH_KEYS_KEY => ['foo']
            ],
            $session->variables
        );
        $session->flash('baz', 'blah');
        $session->ageFlashData();
        $this->assertNull($session->getVariable('foo'));
        $this->assertFalse($session->containsKey('foo'));
        $this->assertSame('blah', $session->getVariable('baz'));
        $this->assertEquals(
            [
                'baz' => 'blah',
                Session::NEW_FLASH_KEYS_KEY => [],
                Session::STALE_FLASH_KEYS_KEY => ['baz']
            ],
            $session->variables
        );
        $this->assertTrue($session->containsKey('baz'));
        $session->ageFlashData();
        $this->assertNull($session->getVariable('baz'));
        $this->assertFalse($session->containsKey('baz'));
        $this->assertEquals(
            [
                Session::NEW_FLASH_KEYS_KEY => [],
                Session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->variables
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
        $session->setVariable('foo', 'bar');
        $session->delete('foo');
        $this->assertFalse($session->containsKey('foo'));
        $this->assertEquals([], $session->variables);
    }

    public function testFlashingDataAndGettingItMakesItStillAccessibleOneMoreTime(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('bar', $session->getVariable('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                Session::NEW_FLASH_KEYS_KEY => ['foo'],
                Session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->variables
        );
    }

    public function testFlushingRemovesAllDataFromSession(): void
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $session->flush();
        $this->assertFalse(isset($session['foo']));
        $this->assertEquals([], $session->variables);
    }

    public function testGettingAllReturnsAllSetKeys(): void
    {
        $session = new Session();
        $session->setVariable('foo', 'bar');
        $session->setVariable('baz', 'blah');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $session->variables);
    }

    public function testGettingIdReturnsSameOneAsSet(): void
    {
        $constructorId = \str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')->willReturn(true);
        $session = new Session($constructorId, $idGenerator);
        $setterId = \str_repeat('2', IIdGenerator::MIN_LENGTH);
        $session->id = $setterId;
        $this->assertSame($setterId, $session->id);
    }

    public function testGettingIdUsesIdGeneratorValue(): void
    {
        $id = \str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')
            ->with($id)
            ->willReturn(true);
        $session = new Session($id, $idGenerator);
        $this->assertSame($id, $session->id);
    }

    public function testGettingNonExistentKeyReturnsNull(): void
    {
        $session = new Session();
        $this->assertNull($session['non-existent']);
        $this->assertNull($session->getVariable('non-existent'));
    }

    public function testGettingNonExistentKeyWithDefaultValueReturnsThatValue(): void
    {
        $session = new Session();
        $this->assertSame('bar', $session->getVariable('foo', 'bar'));
    }

    public function testReflashingKeepsTheDataInSessionUntilItIsAgedAgain(): void
    {
        $session = new Session();
        $session->flash('foo', 'bar');
        $session->ageFlashData();
        $session->reflash();
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('bar', $session->getVariable('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                Session::NEW_FLASH_KEYS_KEY => ['foo'],
                Session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->variables
        );
        $session->ageFlashData();
        $this->assertTrue($session->containsKey('foo'));
        $this->assertSame('bar', $session->getVariable('foo'));
        $this->assertEquals(
            [
                'foo' => 'bar',
                Session::NEW_FLASH_KEYS_KEY => [],
                Session::STALE_FLASH_KEYS_KEY => ['foo']
            ],
            $session->variables
        );
        $session->ageFlashData();
        $this->assertFalse($session->containsKey('foo'));
        $this->assertNull($session->getVariable('foo'));
        $this->assertEquals(
            [
                Session::NEW_FLASH_KEYS_KEY => [],
                Session::STALE_FLASH_KEYS_KEY => []
            ],
            $session->variables
        );
    }

    public function testRegenerateIdUsesIdGeneratorValue(): void
    {
        $generatedId = \str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')
            ->willReturnMap([
                [null, false],
                [$generatedId, true]
            ]);
        $idGenerator->method('generate')->willReturn($generatedId);
        $session = new Session(null, $idGenerator);
        $session->regenerateId();
        $this->assertSame($generatedId, $session->id);
    }

    public function testRegeneratingIdWithDefaultIdGeneratorCreatesANewId(): void
    {
        $session = new Session();
        $session->regenerateId();
        $this->assertIsString($session->id);
    }

    public function testSettingInvalidIdCausesNewIdToBeGenerated(): void
    {
        $generatedId = \str_repeat('1', IIdGenerator::MIN_LENGTH);
        $idGenerator = $this->createMock(IIdGenerator::class);
        $idGenerator->method('idIsValid')
            ->willReturnMap([
                [1, false],
                [2, false],
                [$generatedId, true]
            ]);
        $idGenerator->method('generate')->willReturn($generatedId);
        $session = new Session(1, $idGenerator);
        $this->assertNotEquals(1, $session->id);
        $session->id = 2;
        $this->assertNotEquals(2, $session->id);
    }

    public function testSettingKeyWritesItToSession(): void
    {
        $session = new Session();
        $session->setVariable('foo', 'bar');
        $this->assertSame('bar', $session->getVariable('foo'));
        $this->assertEquals(['foo' => 'bar'], $session->variables);
    }

    public function testSettingManyWritesAllValuesToSession(): void
    {
        $session = new Session();
        $session->setVariable('foo', 'bar');
        $session->addManyVariables(['baz' => 'blah']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $session->variables);
        $session->addManyVariables(['foo' => 'somethingnew']);
        $this->assertEquals(['foo' => 'somethingnew', 'baz' => 'blah'], $session->variables);
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
        $this->assertSame('bar', $session->getVariable('foo'));
        $this->assertEquals(['foo' => 'bar'], $session->variables);
    }

    public function testUnsettingOffsetRemovesTheKeyFromSession(): void
    {
        $session = new Session();
        $session['foo'] = 'bar';
        unset($session['foo']);
        $this->assertNull($session['foo']);
        $this->assertEquals([], $session->variables);
    }
}
