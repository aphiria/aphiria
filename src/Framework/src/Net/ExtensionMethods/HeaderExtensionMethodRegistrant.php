<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Net\ExtensionMethods;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\Net\Http\Formatting\HeaderParser;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;

/**
 * Defines the registrant for header extension methods
 */
class HeaderExtensionMethodRegistrant
{
    /** @var HeaderParser The header parser */
    private HeaderParser $headerParser;

    /**
     * @param HeaderParser|null $headerParser The header parser to use
     */
    public function __construct(HeaderParser $headerParser = null)
    {
        $this->headerParser = $headerParser ?? new HeaderParser();
    }

    /**
     * Registers the extension methods
     */
    public function registerExtensionMethods(): void
    {
        // Because $this will be rebound in the closures, let's set the header parser to a local variable
        $headerParser = $this->headerParser;
        /** @var Headers $this This will be rebound to an instance of Headers */
        ExtensionMethodRegistry::registerExtensionMethod(
            Headers::class,
            'isJson',
            fn (): bool => $headerParser->isJson($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            Headers::class,
            'isMultipart',
            fn (): bool => $headerParser->isMultipart($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            Headers::class,
            'parseContentTypeHeader',
            fn (): ?ContentTypeHeaderValue => $headerParser->parseContentTypeHeader($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            Headers::class,
            'parseParameters',
            fn (string $headerName, int $index = 0): IImmutableDictionary => $headerParser->parseParameters($this, $headerName, $index)
        );
    }
}
