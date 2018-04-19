<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use InvalidArgumentException;
use Opulence\Net\Http\Formatting\Contracts\JsonSerializer;
use Opulence\Net\Http\Formatting\Contracts\SerializationException;

/**
 * Tests the JSON serializer
 */
class JsonSerializerTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsonSerializer The serializer to use in tests */
    private $serializer;

    public function setUp(): void
    {
        $this->serializer = new JsonSerializer();
    }

    public function testDeserializingContractCreatesInstanceOfContractFromDecodedValue(): void
    {
        $contractsAndValues = [
            [ArrayContract::class, '[1,2]', [1, 2]],
            [BoolContract::class, 'true', true],
            [DictionaryContract::class, '{"foo":"bar"}', ['foo' => 'bar']],
            [FloatContract::class, '1.5', 1.5],
            [IntContract::class, '1', 1],
            [StringContract::class, '"foo"', 'foo'],
        ];

        foreach ($contractsAndValues as $contractAndValues) {
            $contract = $this->serializer->deserialize($contractAndValues[1], $contractAndValues[0]);
            $this->assertInstanceOf($contractAndValues[0], $contract);
            $this->assertEquals($contractAndValues[2], $contract->getValue());
        }
    }

    public function testDeserializingInvalidJsonThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserialize('"', StringContract::class);
    }

    public function testDeserializingToContractTypeThatDoesNotImplementIContractThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->serializer->deserialize('foo', 'bar');
    }

    public function testDeserializingToDictionaryContractWithValueThatIsNotDictionaryThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserialize('"foo"', DictionaryContract::class);
    }

    public function testSerializingContractJsonEncodesValueInContract(): void
    {
        /** @var IContract $contract */
        $contract = $this->createMock(IContract::class);
        $contract->expects($this->once())
            ->method('getValue')
            ->willReturn(['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', $this->serializer->serialize($contract));
    }
}
