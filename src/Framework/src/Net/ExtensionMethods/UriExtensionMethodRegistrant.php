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
use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Uri;

/**
 * Defines the registrant for URI extension methods
 */
class UriExtensionMethodRegistrant
{
    /** @var UriParser The URI parser */
    private UriParser $uriParser;

    /**
     * @param UriParser|null $uriParser The URI parser to use
     */
    public function __construct(UriParser $uriParser = null)
    {
        $this->uriParser = $uriParser ?? new UriParser();
    }

    /**
     * Registers the extension methods
     */
    public function registerExtensionMethods(): void
    {
        // Because $this will be rebound in the closures, let's set the URI parser to a local variable
        $uriParser = $this->uriParser;
        /** @var Uri $this This will be rebound to an instance of Uri */
        ExtensionMethodRegistry::registerExtensionMethod(
            Uri::class,
            'parseQueryString',
            fn (): IImmutableDictionary => $uriParser->parseQueryString($this)
        );
    }
}
