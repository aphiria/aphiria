<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Serialization\Normalizers;

use Aphiria\Api\Errors\ProblemDetails;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Defines the problem details normalizer
 */
final class ProblemDetailsNormalizer extends ObjectNormalizer
{
    /**
     * @inheritdoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (!$object instanceof ProblemDetails) {
            throw new InvalidArgumentException('Object must be an instance of ' . ProblemDetails::class);
        }

        /** @var array<string, mixed> $normalizedProblemDetails */
        $normalizedProblemDetails = parent::normalize($object);

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
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof ProblemDetails;
    }
}
