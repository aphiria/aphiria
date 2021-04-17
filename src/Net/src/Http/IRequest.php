<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\Collections\IDictionary;
use Aphiria\Collections\IImmutableDictionary;
use Aphiria\ExtensionMethods\IExtendable;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Uri;

/**
 * Defines the interface for HTTP request messages to implement
 *
 * @method string|null getActualMimeType() Gets the MIME type of the body
 * @method string|null getClientIPAddress() Gets the client IP address from the request
 * @method string|null getClientMimeType() Gets the MIME type that was specified by the client (eg browser)
 * @method bool isJson() Gets whether or not the headers have a JSON content type
 * @method bool isMultipart() Gets whether or not the message is a multipart message
 * @method AcceptCharsetHeaderValue[] parseAcceptCharsetHeader() Parses the Accept-Charset header
 * @method AcceptMediaTypeHeaderValue[] parseAcceptHeader() Parses the Accept header
 * @method AcceptLanguageHeaderValue[] parseAcceptLanguageHeader() Parses the Accept-Language header
 * @method ContentTypeHeaderValue|null parseContentTypeHeader() Parses the Content-Type header
 * @method IImmutableDictionary parseCookies() Parses the request headers for all cookie values
 * @method IImmutableDictionary parseParameters(string $headerName, int $index = 0) Parses the parameters (semi-colon delimited values for a header) for the first value of a header
 * @method IImmutableDictionary parseQueryString() Parses a request's URI's query string into a collection
 * @method IDictionary readAsFormInput() Parses a request body as form input
 * @method array readAsJson() Attempts to read the request body as JSON
 * @method MultipartBody|null readAsMultipart() Parses a request as a multipart request
 */
interface IRequest extends IHttpMessage, IExtendable
{
    /**
     * Gets the HTTP method for the request
     *
     * @return string The HTTP method
     */
    public function getMethod(): string;

    /**
     * Gets the properties of the request
     * These are custom pieces of metadata that the application can attach to the request
     *
     * @return IDictionary The collection of properties
     */
    public function getProperties(): IDictionary;

    /**
     * Gets the URI of the request
     *
     * @return Uri The URI
     */
    public function getUri(): Uri;
}
