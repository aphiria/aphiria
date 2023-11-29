<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Binders\Mocks;

use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MockNormalizer implements NormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function getSupportedTypes(?string $format): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        return ['foo' => 'bar'];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return true;
    }
}
