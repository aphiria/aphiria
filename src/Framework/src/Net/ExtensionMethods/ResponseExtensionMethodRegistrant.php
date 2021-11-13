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

use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\Headers\SameSiteMode;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Uri;

/**
 * Defines the registrant for response extension methods
 */
class ResponseExtensionMethodRegistrant
{
    /** @var ResponseFormatter The response formatter */
    private ResponseFormatter $responseFormatter;

    /**
     * @param ResponseFormatter|null $responseFormatter The response formatter to use
     */
    public function __construct(ResponseFormatter $responseFormatter = null)
    {
        $this->responseFormatter = $responseFormatter ?? new ResponseFormatter();
    }

    /**
     * Registers the extension methods
     */
    public function registerExtensionMethods(): void
    {
        // Because $this will be rebound in the closures, let's set the response formatter to a local variable
        $responseFormatter = $this->responseFormatter;
        /** @var IResponse $this This will be rebound to an instance of IResponse */
        ExtensionMethodRegistry::registerExtensionMethod(
            IResponse::class,
            'deleteCookie',
            fn (
                string $name,
                ?string $path = null,
                ?string $domain = null,
                bool $isSecure = false,
                bool $isHttpOnly = true,
                ?SameSiteMode $sameSite = null
            ) => $responseFormatter->deleteCookie($this, $name, $path, $domain, $isSecure, $isHttpOnly, $sameSite)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IResponse::class,
            'setCookie',
            fn (Cookie $cookie) => $responseFormatter->setCookie($this, $cookie)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IResponse::class,
            'setCookies',
            /** @param list<Cookie> $cookies */
            fn (array $cookies) => $responseFormatter->setCookies($this, $cookies)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IResponse::class,
            'redirectToUri',
            fn (string|Uri $uri, int $statusCode = 302) => $responseFormatter->redirectToUri($this, $uri, $statusCode)
        );
        ExtensionMethodRegistry::registerExtensionMethod(
            IResponse::class,
            'writeJson',
            fn (array $content) => $responseFormatter->writeJson($this, $content)
        );
    }
}
