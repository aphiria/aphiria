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
use Opulence\Net\Http\Formatting\Contracts\ArrayContract;
use Opulence\Net\Http\Formatting\Contracts\BoolContract;
use Opulence\Net\Http\Formatting\Contracts\DictionaryContract;
use Opulence\Net\Http\Formatting\Contracts\FloatContract;
use Opulence\Net\Http\Formatting\Contracts\IContract;
use Opulence\Net\Http\Formatting\Contracts\IntContract;
use Opulence\Net\Http\Formatting\Contracts\JsonContractSerializer;
use Opulence\Net\Http\Formatting\Contracts\SerializationException;
use Opulence\Net\Http\Formatting\Contracts\StringContract;

/**
 * Tests the JSON contract serializer
 */
class JsonContractSerializerTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsonContractSerializer The serializer to use in tests */
    private $serializer;

    public function setUp(): void
    {
        $this->serializer = new JsonContractSerializer();
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
            $contract = $this->serializer->deserializeContract($contractAndValues[1], $contractAndValues[0]);
            $this->assertInstanceOf($contractAndValues[0], $contract);
            $this->assertEquals($contractAndValues[2], $contract->getValue());
        }
    }

    public function testDeserializingInvalidJsonThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserializeContract('"', StringContract::class);
    }

    public function testDeserializingToContractTypeThatDoesNotImplementIContractThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->serializer->deserializeContract('foo', 'bar');
    }

    public function testDeserializingToDictionaryContractWithValueThatIsNotDictionaryThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserializeContract('"foo"', DictionaryContract::class);
    }

    public function testSerializingContractJsonEncodesValueInContract(): void
    {
        /** @var IContract $contract */
        $contract = $this->createMock(IContract::class);
        $contract->expects($this->once())
            ->method('getValue')
            ->willReturn(['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', $this->serializer->serializeContract($contract));
    }
}
