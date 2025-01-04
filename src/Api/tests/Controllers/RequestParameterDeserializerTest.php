<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\RequestParameterDeserializer;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RequestParameterDeserializerTest extends TestCase
{
    public static function boolProvider(): array
    {
        return [
            [true, true],
            [false, false],
            ['1', true],
            ['0', false],
            [1, true],
            [0, false],
            ['true', true],
            ['false', false],
            ['yes', true],
            ['no', false],
            ['y', true],
            ['n', false]
        ];
    }

    /**
     * @param mixed $rawValue The raw value
     * @param bool $expectedDeserializedValue The expected deserialized value
     */
    #[DataProvider('boolProvider')]
    public function testBoolsDeserializeByDefault(mixed $rawValue, bool $expectedDeserializedValue): void
    {
        $deserializer = new RequestParameterDeserializer();
        $this->assertSame($expectedDeserializedValue, $deserializer->deserializeRouteActionParameter('bool', $rawValue));
    }

    public function testFloatsDeserializeByDefault(): void
    {
        $deserializer = new RequestParameterDeserializer();
        $this->assertSame(1.0, $deserializer->deserializeRouteActionParameter('float', 1.0));
        $this->assertSame(1.0, $deserializer->deserializeRouteActionParameter('float', '1.0'));
    }

    public function testIntsDeserializeByDefault(): void
    {
        $deserializer = new RequestParameterDeserializer();
        $this->assertSame(1, $deserializer->deserializeRouteActionParameter('int', 1));
        $this->assertSame(1, $deserializer->deserializeRouteActionParameter('int', '1'));
    }

    public function testRegisteringDeserializerForTypeWithBuiltInDeserializerOverwritesIt(): void
    {
        $deserializer = new RequestParameterDeserializer();
        $deserializer->registerDeserializer('int', fn (mixed $value): int => (int)$value + 1);
        $this->assertSame(2, $deserializer->deserializeRouteActionParameter('int', 1));
    }

    public function testRegisteringDeserializerForTypeWithoutBuiltInDeserializerCanDeserializeValues(): void
    {
        $deserializer = new RequestParameterDeserializer();
        $deserializer->registerDeserializer(DateTime::class, fn (mixed $value): DateTime => DateTime::createFromFormat('Y-m-d', $value));
        $deserializedDateTime = $deserializer->deserializeRouteActionParameter(DateTime::class, '2025-01-03');
        $this->assertSame('2025', $deserializedDateTime->format('Y'));
        $this->assertSame('01', $deserializedDateTime->format('m'));
        $this->assertSame('03', $deserializedDateTime->format('d'));
    }

    public function testStringsDeserializeByDefault(): void
    {
        $deserializer = new RequestParameterDeserializer();
        $this->assertSame('1', $deserializer->deserializeRouteActionParameter('string', 1));
        $this->assertSame('1', $deserializer->deserializeRouteActionParameter('string', '1'));
    }
}
