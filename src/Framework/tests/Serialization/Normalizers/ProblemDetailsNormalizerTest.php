<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Normalizers;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Framework\Serialization\Normalizers\ProblemDetailsNormalizer;
use Aphiria\Net\Http\HttpStatusCodes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Serializer;

class ProblemDetailsNormalizerTest extends TestCase
{
    private ProblemDetailsNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ProblemDetailsNormalizer();
        $this->normalizer->setSerializer(new Serializer([$this->normalizer], []));
    }

    public function testNormalizeAddsExtensionsToTopLevelOfNormalizedArray(): void
    {
        $problemDetails = new ProblemDetails('foo', extensions: ['foo' => 'bar']);
        $expectedNormalizedValue = [
            'type' => 'foo',
            'title' => null,
            'detail' => null,
            'status' => HttpStatusCodes::INTERNAL_SERVER_ERROR,
            'instance' => null,
            'foo' => 'bar'
        ];
        $this->assertEquals($expectedNormalizedValue, $this->normalizer->normalize($problemDetails));
    }

    public function testNormalizeDoesNotAddAnyExtensionsForProblemDetailsWithNoExtensions(): void
    {
        $problemDetails = new ProblemDetails('foo');
        $expectedNormalizedValue = [
            'type' => 'foo',
            'title' => null,
            'detail' => null,
            'status' => HttpStatusCodes::INTERNAL_SERVER_ERROR,
            'instance' => null
        ];
        $this->assertEquals($expectedNormalizedValue, $this->normalizer->normalize($problemDetails));
    }

    public function testNormalizeThrowsExceptionIfObjectIsNotInstanceOfProblemDetails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Object must be an instance of ' . ProblemDetails::class);
        $this->normalizer->normalize($this);
    }

    public function testSupportsNormalizationReturnsTrueOnlyIfDataIsProblemDetailsInstance(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization($this));
        $this->assertTrue($this->normalizer->supportsNormalization(new ProblemDetails()));
    }
}
