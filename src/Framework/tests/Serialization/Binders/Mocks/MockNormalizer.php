<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Binders\Mocks;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MockNormalizer implements NormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return ['foo' => 'bar'];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return true;
    }
}
