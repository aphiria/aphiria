<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the various HTTP status codes
 */
final class HttpStatusCodes
{
    /** @const Continue */
    public const HTTP_CONTINUE = 100;
    /** @const Switching protocol */
    public const HTTP_SWITCHING_PROTOCOL = 101;
    /** @const Successful response */
    public const HTTP_OK = 200;
    /** @const Request has been fulfilled and a new resource has been created */
    public const HTTP_CREATED = 201;
    /** @const The request has been accepted for processing, but processing hasn't completed */
    public const HTTP_ACCEPTED = 202;
    /** @const The response was collected from a copy */
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    /** @const No content */
    public const HTTP_NO_CONTENT = 204;
    /** @const After accomplishing request to tell user agent reset document view which sent the request */
    public const HTTP_RESET_CONTENT = 205;
    /** @const The request contains partial content */
    public const HTTP_PARTIAL_CONTENT = 206;
    /** @const The request contains multiple status codes */
    public const HTTP_MULTI_STATUS = 207;
    /** @const The members of a DAV binding have been enumerated in a preceding part of the multi-status response */
    public const HTTP_ALREADY_REPORTED = 208;
    /** @const The server has fulfilled a request for the resource, and the response represents one or more instance-manipulations */
    public const HTTP_IM_USED = 226;
    /** @const Multiple choice redirect */
    public const HTTP_MULTIPLE_CHOICE = 300;
    /** @const Moved permanently */
    public const HTTP_MOVED_PERMANENTLY = 301;
    /** @const The URI has been changed temporarily */
    public const HTTP_FOUND = 302;
    /** @const See other */
    public const HTTP_SEE_OTHER = 303;
    /** @const The response has not been modified */
    public const HTTP_NOT_MODIFIED = 304;
    /** @const The response must be accept by a proxy */
    public const HTTP_USE_PROXY = 305;
    /** @const A temporary redirect */
    public const HTTP_TEMPORARY_REDIRECT = 307;
    /** @const The request URI is now permanently at another URI */
    public const HTTP_PERMANENT_REDIRECT = 308;
    /** @const The request was bad */
    public const HTTP_BAD_REQUEST = 400;
    /** @const The request requires authentication */
    public const HTTP_UNAUTHORIZED = 401;
    /** @const Payment is required */
    public const HTTP_PAYMENT_REQUIRED = 402;
    /** @const The server understood the request, but is refusing to fulfill it */
    public const HTTP_FORBIDDEN = 403;
    /** @const The server didn't find anything matching the request URI */
    public const HTTP_NOT_FOUND = 404;
    /** @const The method is not allowed */
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    /** @const Cannot find content with the criteria from the user agent */
    public const HTTP_NOT_ACCEPTABLE = 406;
    /** @const Authentication needs to be done via a proxy */
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    /** @const The request timed out */
    public const HTTP_REQUEST_TIMEOUT = 408;
    /** @const There's a conflict with the state of the server */
    public const HTTP_CONFLICT = 409;
    /** @const The content has been deleted from the server */
    public const HTTP_GONE = 410;
    /** @const The The content-length header was required wasn't defined */
    public const HTTP_LENGTH_REQUIRED = 411;
    /** @const Preconditions in the headers were not met */
    public const HTTP_PRECONDITION_FAILED = 412;
    /** @const The request entity was too large */
    public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    /** @const The request URI was too long */
    public const HTTP_URI_TOO_LONG = 414;
    /** @const The request media format wasn't supported */
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    /** @const The range header cannot be fulfilled */
    public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    /** @const The expected header cannot be met */
    public const HTTP_EXPECTATION_FAILED = 417;
    /** @const The request is a teapot, and totally legit */
    public const HTTP_TEAPOT = 418;
    /** @const The request was directed at a server that is not able to produce a response */
    public const HTTP_MISDIRECTED_REQUEST = 421;
    /** @const The request was well-formed, but was unable to be followed due to semantic errors */
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    /** @const The resource that is being accessed is locked */
    public const HTTP_LOCKED = 423;
    /** @const The request failed because it depended on another request that failed */
    public const HTTP_FAILED_DEPENDENCY = 424;
    /** @const The client should switch to a different protocol */
    public const HTTP_UPGRADE_REQUIRED = 426;
    /** @const The origin server requires the request to be conditional */
    public const HTTP_PRECONDITION_REQUIRED = 428;
    /** @const The user has sent too many requests in a given amount of time */
    public const HTTP_TOO_MANY_REQUESTS = 429;
    /** @const The server is unwilling to process the request the header fields are too large */
    public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    /** @const The server has received a legal demand to deny access to a resource */
    public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    /** @const The server encountered an unexpected condition which prevented it from fulfilling the request */
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    /** @const The server does not support the functionality required to fulfill the request */
    public const HTTP_NOT_IMPLEMENTED = 501;
    /** @const The server acted as a gateway and got an invalid response */
    public const HTTP_BAD_GATEWAY = 502;
    /** @const The server is currently unable to handle the request due to a temporary overloading/maintenance */
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    /** @const The server acted as a gateway and timed out */
    public const HTTP_GATEWAY_TIMEOUT = 504;
    /** @const The HTTP version in the request isn't supported */
    public const HTTP_HTTP_VERSION_NOT_SUPPORTED = 505;
    /** @const Transparent content negotiation for the request results in a circular reference */
    public const HTTP_VARIANT_ALSO_NEGOTIATES = 506;
    /** @const The server is unable to store the representation needed to complete the request */
    public const HTTP_INSUFFICIENT_STORAGE = 507;
    /** @const The server detected an infinite loop while processing the request */
    public const HTTP_LOOP_DETECTED = 508;
    /** @const Further extensions to the request are required for the server to fulfil it */
    public const HTTP_NOT_EXTENDED = 510;
    /** @const The client needs to authenticate to gain network access */
    public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
    /** @var array Maps HTTP status codes to their default reason phrases */
    private static array $defaultReasonPhrases = [
        self::HTTP_CONTINUE => 'Continue',
        self::HTTP_SWITCHING_PROTOCOL => 'Switching Protocol',
        self::HTTP_OK => 'OK',
        self::HTTP_CREATED => 'Created',
        self::HTTP_ACCEPTED => 'Accepted',
        self::HTTP_NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::HTTP_NO_CONTENT => 'No Content',
        self::HTTP_RESET_CONTENT => 'Reset Content',
        self::HTTP_PARTIAL_CONTENT => 'Partial Content',
        self::HTTP_MULTIPLE_CHOICE => 'Multiple Choice',
        self::HTTP_MOVED_PERMANENTLY => 'Moved Permanently',
        self::HTTP_FOUND => 'Found',
        self::HTTP_SEE_OTHER => 'See Other',
        self::HTTP_NOT_MODIFIED => 'Not Modified',
        self::HTTP_USE_PROXY => 'Use Proxy',
        self::HTTP_TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::HTTP_PERMANENT_REDIRECT => 'Permanent Redirect',
        self::HTTP_BAD_REQUEST => 'Bad Request',
        self::HTTP_UNAUTHORIZED => 'Unauthorized',
        self::HTTP_PAYMENT_REQUIRED => 'Payment Required',
        self::HTTP_FORBIDDEN => 'Forbidden',
        self::HTTP_NOT_FOUND => 'Not Found',
        self::HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::HTTP_NOT_ACCEPTABLE => 'Not Acceptable',
        self::HTTP_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::HTTP_REQUEST_TIMEOUT => 'Request Timeout',
        self::HTTP_CONFLICT => 'Conflict',
        self::HTTP_GONE => 'Gone',
        self::HTTP_LENGTH_REQUIRED => 'Length Required',
        self::HTTP_PRECONDITION_FAILED => 'Precondition Failed',
        self::HTTP_REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::HTTP_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::HTTP_EXPECTATION_FAILED => 'Expectation Failed',
        self::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::HTTP_NOT_IMPLEMENTED => 'Not Implemented',
        self::HTTP_BAD_GATEWAY => 'Bad Gateway',
        self::HTTP_SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::HTTP_GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::HTTP_HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported'
    ];

    /**
     * Gets the default reason phrase for a status code
     *
     * @param int $statusCode The status code whose reason phrase we want
     * @return string|null The default reason code if one exists, otherwise null
     */
    public static function getDefaultReasonPhrase(int $statusCode): ?string
    {
        return self::$defaultReasonPhrases[$statusCode] ?? null;
    }
}
