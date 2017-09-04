<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

/**
 * Defines the various HTTP status codes
 */
class HttpStatusCodes
{
    /** Continue */
    public const HTTP_CONTINUE = 100;
    /** Switching protocol */
    public const HTTP_SWITCHING_PROTOCOL = 101;
    /** Successful response */
    public const HTTP_OK = 200;
    /** Request has been fulfilled and a new resource has been created */
    public const HTTP_CREATED = 201;
    /** The request has been accepted for processing, but processing hasn't completed */
    public const HTTP_ACCEPTED = 202;
    /** The response was collected from a copy */
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    /** No content */
    public const HTTP_NO_CONTENT = 204;
    /** After accomplishing request to tell user agent reset document view which sent the request */
    public const HTTP_RESET_CONTENT = 205;
    /** The request contains partial content */
    public const HTTP_PARTIAL_CONTENT = 206;
    /** Multiple choice redirect */
    public const HTTP_MULTIPLE_CHOICE = 300;
    /** Moved permanently */
    public const HTTP_MOVED_PERMANENTLY = 301;
    /** The URI has been changed temporarily */
    public const HTTP_FOUND = 302;
    /** See other */
    public const HTTP_SEE_OTHER = 303;
    /** The response has not been modified */
    public const HTTP_NOT_MODIFIED = 304;
    /** The response must be accept by a proxy */
    public const HTTP_USE_PROXY = 305;
    /** A temporary redirect */
    public const HTTP_TEMPORARY_REDIRECT = 307;
    /** The request URI is now permanently at another URI */
    public const HTTP_PERMANENT_REDIRECT = 308;
    /** The request was bad */
    public const HTTP_BAD_REQUEST = 400;
    /** The request requires authentication */
    public const HTTP_UNAUTHORIZED = 401;
    /** Payment is required */
    public const HTTP_PAYMENT_REQUIRED = 402;
    /** The server understood the request, but is refusing to fulfill it */
    public const HTTP_FORBIDDEN = 403;
    /** The server didn't find anything matching the request URI */
    public const HTTP_NOT_FOUND = 404;
    /** The method is not allowed */
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    /** Cannot find content with the criteria from the user agent */
    public const HTTP_NOT_ACCEPTABLE = 406;
    /** Authentication needs to be done via a proxy */
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    /** The request timed out */
    public const HTTP_REQUEST_TIMEOUT = 408;
    /** There's a conflict with the state of the server */
    public const HTTP_CONFLICT = 409;
    /** The content has been deleted from the server */
    public const HTTP_GONE = 410;
    /** The The content-length header was required wasn't defined */
    public const HTTP_LENGTH_REQUIRED = 411;
    /** Preconditions in the headers were not met */
    public const HTTP_PRECONDITION_FAILED = 412;
    /** The request entity was too large */
    public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    /** The request media format wasn't supported */
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    /** The range header cannot be fulfilled */
    public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    /** The expected header cannot be met */
    public const HTTP_EXPECTATION_FAILED = 417;
    /** The server encountered an unexpected condition which prevented it from fulfilling the request */
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    /** The server does not support the functionality required to fulfill the request */
    public const HTTP_NOT_IMPLEMENTED = 501;
    /** The server acted as a gateway and got an invalid response */
    public const HTTP_BAD_GATEWAY = 502;
    /** The server is currently unable to handle the request due to a temporary overloading/maintenance */
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    /** The server acted as a gateway and timed out */
    public const HTTP_GATEWAY_TIMEOUT = 504;
    /** The HTTP version in the request isn't supported */
    public const HTTP_HTTP_VERSION_NOT_SUPPORTED = 505;
}
