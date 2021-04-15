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
use Aphiria\Collections\IImmutableDictionary;
use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\MultipartBody;

/**
 * Defines the registrant for request extension methods
 */
class RequestExtensionMethodRegistrant
{
    /** @var RequestParser The request parser */
    private RequestParser $requestParser;

    /**
     * @param RequestParser|null $requestParser The request parser to use
     */
    public function __construct(RequestParser $requestParser = null)
    {
        $this->requestParser = $requestParser ?? new RequestParser();
    }

    /**
     * Registers the extension methods
     */
    public function registerExtensionMethods(): void
    {
        // Because $this will be rebound in the closures, let's set the request parser to a local variable
        $requestParser = $this->requestParser;
        /** @var IRequest $this This will be rebound to an instance of IRequest */
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'getActualMimeType',
            fn (): ?string => $requestParser->getActualMimeType($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'getClientIPAddress',
            fn (): ?string => $requestParser->getClientIPAddress($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'isJson',
            fn (): bool => $requestParser->isJson($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'isMultipart',
            fn (): bool => $requestParser->isMultipart($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'parseAcceptCharsetHeader',
            fn (): array => $requestParser->parseAcceptCharsetHeader($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'parseAcceptHeader',
            fn (): array => $requestParser->parseAcceptHeader($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'parseAcceptLanguageHeader',
            fn (): array => $requestParser->parseAcceptLanguageHeader($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'parseContentTypeHeader',
            fn (): ?ContentTypeHeaderValue => $requestParser->parseContentTypeHeader($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'parseCookies',
            fn (): IImmutableDictionary => $requestParser->parseCookies($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'parseParameters',
            fn (string $headerName, int $index = 0): IImmutableDictionary => $requestParser->parseParameters($this, $headerName, $index)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'parseQueryString',
            fn (): IImmutableDictionary => $requestParser->parseQueryString($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'readAsFormInput',
            fn (): IDictionary => $requestParser->readAsFormInput($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'readAsJson',
            fn (): array => $requestParser->readAsJson($this)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IRequest::class,
            'readAsMultipart',
            fn (): ?MultipartBody => $requestParser->readAsMultipart($this)
        );
    }
}
