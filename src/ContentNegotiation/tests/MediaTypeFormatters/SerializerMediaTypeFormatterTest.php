<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests\MediaTypeFormatters;

use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializerMediaTypeFormatter;
use Aphiria\IO\Streams\Stream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerMediaTypeFormatterTest extends TestCase
{
    private SerializerMediaTypeFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new class () extends SerializerMediaTypeFormatter {
            public function __construct()
            {
                parent::__construct(new Serializer([new ObjectNormalizer()]), 'foo');
            }

            public function canReadType(string $type): bool
            {
                return false;
            }

            public function canWriteType(string $type): bool
            {
                return false;
            }

            public function getSupportedEncodings(): array
            {
                return ['utf-8'];
            }

            public function getSupportedMediaTypes(): array
            {
                return ['text/plain'];
            }
        };
    }

    public function testReadingToStreamForUnsupportedTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($this->formatter::class . ' cannot read type ' . self::class);
        $this->formatter->readFromStream(new Stream(\fopen('php://temp', 'r+b')), self::class);
    }

    public function testWritingToStreamForUnsupportedTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($this->formatter::class . ' cannot write type ' . self::class);
        $this->formatter->writeToStream($this, new Stream(\fopen('php://temp', 'r+b')), null);
    }
}
