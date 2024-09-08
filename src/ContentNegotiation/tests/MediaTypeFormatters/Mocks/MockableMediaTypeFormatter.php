<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests\MediaTypeFormatters\Mocks;

use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;

/**
 * Defines a media type formatter that is suitable for mocking
 */
abstract class MockableMediaTypeFormatter implements IMediaTypeFormatter
{
    public string $defaultEncoding = '';
    public string $defaultMediaType = '';
    public array $supportedEncodings = [];
    public array $supportedMediaTypes = [];
}
