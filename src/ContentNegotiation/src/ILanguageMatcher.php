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

use Aphiria\Net\Http\IRequest;

/**
 * Defines the interface for language matchers to implement
 */
interface ILanguageMatcher
{
    /**
     * Gets the best language match between a list of supported languages and Accept-Lang
     *
     * @param IRequest $request The current request
     * @return string|null The best language match if one existed, otherwise null
     * @link https://tools.ietf.org/html/rfc4647#section-3.4
     */
    public function getBestLanguageMatch(IRequest $request): ?string;
}
