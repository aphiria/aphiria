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

use Aphiria\Collections\IDictionary;
use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\Net\Http\Formatting\BodyParser;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\MultipartBody;

/**
 * Defines the registrant for body extension methods
 */
class BodyExtensionMethodRegistrant
{
    /** @var BodyParser The body parser */
    private BodyParser $bodyParser;

    /**
     * @param BodyParser|null $bodyParser The body parser to use
     */
    public function __construct(BodyParser $bodyParser = null)
    {
        $this->bodyParser = $bodyParser ?? new BodyParser();
    }

    /**
     * Registers the extension methods
     */
    public function registerExtensionMethods(): void
    {
        // Because $this will be rebound in the closures, let's set the body parser to a local variable
        $bodyParser = $this->bodyParser;
        /** @var IBody $this This will be rebound to an instance of IBody */
        ExtensionMethodRegistry::registerExtensionMethod(
            IBody::class,
            'getMimeType',
            fn (): ?string => $bodyParser->getMimeType($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IBody::class,
            'readAsFormInput',
            fn (): IDictionary => $bodyParser->readAsFormInput($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IBody::class,
            'readAsJson',
            fn (): array => $bodyParser->readAsJson($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IBody::class,
            'readAsMultipart',
            fn (string $boundary): ?MultipartBody => $bodyParser->readAsMultipart($this, $boundary)
        );
    }
}
