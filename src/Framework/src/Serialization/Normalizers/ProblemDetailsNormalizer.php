<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Serialization\Normalizers;

use Aphiria\Api\Errors\ProblemDetails;
use ArrayObject;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Defines the problem details normalizer
 */
final class ProblemDetailsNormalizer implements NormalizerInterface, SerializerAwareInterface, DenormalizerInterface
{
    /**
     * @param ObjectNormalizer $objectNormalizer The normalizer to use
     */
    public function __construct(private readonly ObjectNormalizer $objectNormalizer = new ObjectNormalizer())
    {
    }

    /**
     * @inheritdoc
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return $this->objectNormalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * @inheritdoc
     * @return array<string, bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [ProblemDetails::class => true];
    }

    /**
     * @inheritdoc
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        if (!$object instanceof ProblemDetails) {
            throw new InvalidArgumentException('Object must be an instance of ' . ProblemDetails::class);
        }

        /** @var array<string, mixed> $normalizedProblemDetails */
        $normalizedProblemDetails = $this->objectNormalizer->normalize($object);

        if (\array_key_exists('extensions', $normalizedProblemDetails)) {
            // Remove the extensions in the off chance that there's an extension named 'extensions'
            /** @var array<string, mixed> $extensions */
            $extensions = $normalizedProblemDetails['extensions'];
            unset($normalizedProblemDetails['extensions']);

            // Extensions could technically be null
            if (\is_array($extensions)) {
                /** @psalm-suppress MixedAssignment We're purposely setting the value to a mixed type */
                foreach ($extensions as $name => $value) {
                    $normalizedProblemDetails[$name] = $value;
                }
            }
        }

        return $normalizedProblemDetails;
    }

    /**
     * @inheridoc
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->objectNormalizer->setSerializer($serializer);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === ProblemDetails::class;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof ProblemDetails;
    }
}
