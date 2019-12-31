<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\ContentNegotiation;

use Aphiria\Net\Http\HttpHeaders;

/**
 * Defines the interface for language matchers to implement
 */
interface ILanguageMatcher
{
    /**
     * Gets the best language match between a list of supported languages and Accept-Language headers
     *
     * @param HttpHeaders $requestHeaders The request headers
     * @return string|null The best language match if one existed, otherwise null
     * @link https://tools.ietf.org/html/rfc4647#section-3.4
     */
    public function getBestLanguageMatch(HttpHeaders $requestHeaders): ?string;
}
