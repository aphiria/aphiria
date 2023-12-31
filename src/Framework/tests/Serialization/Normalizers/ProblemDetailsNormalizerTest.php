<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Normalizers;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Framework\Serialization\Normalizers\ProblemDetailsNormalizer;
use Aphiria\Net\Http\HttpStatusCode;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ProblemDetailsNormalizerTest extends TestCase
{
    private ProblemDetailsNormalizer $normalizer;
    private ObjectNormalizer $objectNormalizer;

    protected function setUp(): void
    {
        $this->objectNormalizer = new ObjectNormalizer();
        $this->normalizer = new ProblemDetailsNormalizer($this->objectNormalizer);
        $this->normalizer->setSerializer(new Serializer([$this->normalizer], []));
    }

    public function testCallingDenormalizePassesCallThroughToObjectNormalizer(): void
    {
        $normalizedData = [
            'type' => 'foo',
            'title' => 'title',
            'detail' => 'details',
            'status' => HttpStatusCode::InternalServerError->value,
            'instance' => 'instance',
            'extensions' => ['foo' => 'bar']
        ];
        $expectedProblemDetails = new ProblemDetails('foo', 'title', 'details', HttpStatusCode::InternalServerError, 'instance', ['foo' => 'bar']);
        $this->assertEquals(
            $expectedProblemDetails,
            $this->objectNormalizer->denormalize($normalizedData, ProblemDetails::class)
        );
        $this->assertEquals(
            $expectedProblemDetails,
            $this->normalizer->denormalize($normalizedData, ProblemDetails::class)
        );
    }

    public function testCallingGetSupportedTypesReturnsProblemDetails(): void
    {
        $this->assertSame([ProblemDetails::class => true], $this->normalizer->getSupportedTypes(null));
    }

    public function testCallingSetSerializerPassesCallThroughToObjectNormalizer(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $objectNormalizer = new ObjectNormalizer();
        $normalizer = new ProblemDetailsNormalizer($objectNormalizer);
        $normalizer->setSerializer($serializer);
        $reflection = new ReflectionObject($objectNormalizer);
        $this->assertSame($serializer, $reflection->getProperty('serializer')->getValue($objectNormalizer));
    }

    public function testCallingSupportsDenormalizationOnlyReturnsTrueForProblemDetailsType(): void
    {
        $this->assertTrue($this->normalizer->supportsDenormalization([], ProblemDetails::class));
        $this->assertFalse($this->normalizer->supportsDenormalization([], self::class));
    }

    public function testCallingSupportsNormalizationOnlyAcceptsProblemDetails(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new ProblemDetails()));
        $this->assertFalse($this->normalizer->supportsNormalization([]));
        $this->assertFalse($this->normalizer->supportsNormalization($this));
    }

    public function testNormalizeAddsExtensionsToTopLevelOfNormalizedArray(): void
    {
        $problemDetails = new ProblemDetails('foo', extensions: ['foo' => 'bar']);
        $expectedNormalizedValue = [
            'type' => 'foo',
            'title' => null,
            'detail' => null,
            'status' => HttpStatusCode::InternalServerError->value,
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
            'status' => HttpStatusCode::InternalServerError->value,
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
