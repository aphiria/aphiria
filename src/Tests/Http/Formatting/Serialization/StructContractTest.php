<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\IEncodingInterceptor;
use Opulence\Net\Http\Formatting\Serialization\StructContract;

/**
 * Tests the struct contract
 */
class StructContractTest extends \PHPUnit\Framework\TestCase
{
    /** @var StructContract The contract to use in tests */
    private $contract;

    public function setUp(): void
    {
        $this->contract = new StructContract(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
    }

    public function testDecodedValueIsSentThroughInterceptors(): void
    {
        /** @var IEncodingInterceptor $interceptor */
        $interceptor = $this->createMock(IEncodingInterceptor::class);
        $interceptor->expects($this->at(0))
            ->method('onDecoding')
            ->with(123, 'int')
            ->willReturn(456);
        $this->assertSame(456, $this->contract->decode(123, [$interceptor]));
    }

    public function testDecodingValueUsesValueFactory(): void
    {
        $this->assertSame(123, $this->contract->decode('123'));
    }

    public function testEncodedValueIsSentThroughInterceptors(): void
    {
        /** @var IEncodingInterceptor $interceptor */
        $interceptor = $this->createMock(IEncodingInterceptor::class);
        $interceptor->expects($this->at(0))
            ->method('onEncoding')
            ->with(123, 'int')
            ->willReturn(456);
        $this->assertSame(456, $this->contract->encode(123, [$interceptor]));
    }

    public function testEncodingValueUsesEncodingFactory(): void
    {
        $this->assertSame(123, $this->contract->encode(123));
    }

    public function testGettingTypeReturnsOneSetInConstructor(): void
    {
        $this->assertEquals('int', $this->contract->getType());
    }
}
