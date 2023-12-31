<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Binders\Mocks;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

class MockEncoder implements EncoderInterface
{
    /**
     * @inheritdoc
     */
    public function encode(mixed $data, string $format, array $context = []): string
    {
        return 'foobar';
    }

    /**
     * @inheritdoc
     */
    public function supportsEncoding(string $format): bool
    {
        return true;
    }
}
