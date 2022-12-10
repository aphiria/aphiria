<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;

/**
 * Defines the results of content negotiation
 */
final readonly class ContentNegotiationResult
{
    /**
     * @param IMediaTypeFormatter|null $formatter The matched media type formatter if there was one, otherwise null
     * @param string|null $mediaType The matched media type if there was one, otherwise null
     * @param string|null $encoding The matched encoding if there was one, otherwise null
     * @param string|null $language The matched language if there was one, otherwise null
     */
    public function __construct(
        public ?IMediaTypeFormatter $formatter,
        public ?string $mediaType,
        public ?string $encoding,
        public ?string $language
    ) {
    }
}
