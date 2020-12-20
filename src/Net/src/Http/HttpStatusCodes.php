<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the various HTTP status codes
 */
final class HttpStatusCodes
{
    /** @const Continue */
    public const CONTINUE = 100;
    /** @const Switching protocol */
    public const SWITCHING_PROTOCOL = 101;
    /** @const Successful response */
    public const OK = 200;
    /** @const Request has been fulfilled and a new resource has been created */
    public const CREATED = 201;
    /** @const The request has been accepted for processing, but processing hasn't completed */
    public const ACCEPTED = 202;
    /** @const The response was collected from a copy */
    public const NON_AUTHORITATIVE_INFORMATION = 203;
    /** @const No content */
    public const NO_CONTENT = 204;
    /** @const After accomplishing request to tell user agent reset document view which sent the request */
    public const RESET_CONTENT = 205;
    /** @const The request contains partial content */
    public const PARTIAL_CONTENT = 206;
    /** @const The request contains multiple status codes */
    public const MULTI_STATUS = 207;
    /** @const The members of a DAV binding have been enumerated in a preceding part of the multi-status response */
    public const ALREADY_REPORTED = 208;
    /** @const The server has fulfilled a request for the resource, and the response represents one or more instance-manipulations */
    public const IM_USED = 226;
    /** @const Multiple choice redirect */
    public const MULTIPLE_CHOICE = 300;
    /** @const Moved permanently */
    public const MOVED_PERMANENTLY = 301;
    /** @const The URI has been changed temporarily */
    public const FOUND = 302;
    /** @const See other */
    public const SEE_OTHER = 303;
    /** @const The response has not been modified */
    public const NOT_MODIFIED = 304;
    /** @const The response must be accept by a proxy */
    public const USE_PROXY = 305;
    /** @const A temporary redirect */
    public const TEMPORARY_REDIRECT = 307;
    /** @const The request URI is now permanently at another URI */
    public const PERMANENT_REDIRECT = 308;
    /** @const The request was bad */
    public const BAD_REQUEST = 400;
    /** @const The request requires authentication */
    public const UNAUTHORIZED = 401;
    /** @const Payment is required */
    public const PAYMENT_REQUIRED = 402;
    /** @const The server understood the request, but is refusing to fulfill it */
    public const FORBIDDEN = 403;
    /** @const The server didn't find anything matching the request URI */
    public const NOT_FOUND = 404;
    /** @const The method is not allowed */
    public const METHOD_NOT_ALLOWED = 405;
    /** @const Cannot find content with the criteria from the user agent */
    public const NOT_ACCEPTABLE = 406;
    /** @const Authentication needs to be done via a proxy */
    public const PROXY_AUTHENTICATION_REQUIRED = 407;
    /** @const The request timed out */
    public const REQUEST_TIMEOUT = 408;
    /** @const There's a conflict with the state of the server */
    public const CONFLICT = 409;
    /** @const The content has been deleted from the server */
    public const GONE = 410;
    /** @const The The content-length header was required wasn't defined */
    public const LENGTH_REQUIRED = 411;
    /** @const Preconditions in the headers were not met */
    public const PRECONDITION_FAILED = 412;
    /** @const The request entity was too large */
    public const REQUEST_ENTITY_TOO_LARGE = 413;
    /** @const The request URI was too long */
    public const URI_TOO_LONG = 414;
    /** @const The request media format wasn't supported */
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    /** @const The range header cannot be fulfilled */
    public const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    /** @const The expected header cannot be met */
    public const EXPECTATION_FAILED = 417;
    /** @const The request is a teapot, and totally legit */
    public const TEAPOT = 418;
    /** @const The request was directed at a server that is not able to produce a response */
    public const MISDIRECTED_REQUEST = 421;
    /** @const The request was well-formed, but was unable to be followed due to semantic errors */
    public const UNPROCESSABLE_ENTITY = 422;
    /** @const The resource that is being accessed is locked */
    public const LOCKED = 423;
    /** @const The request failed because it depended on another request that failed */
    public const FAILED_DEPENDENCY = 424;
    /** @const The client should switch to a different protocol */
    public const UPGRADE_REQUIRED = 426;
    /** @const The origin server requires the request to be conditional */
    public const PRECONDITION_REQUIRED = 428;
    /** @const The user has sent too many requests in a given amount of time */
    public const TOO_MANY_REQUESTS = 429;
    /** @const The server is unwilling to process the request the header fields are too large */
    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    /** @const The server has received a legal demand to deny access to a resource */
    public const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    /** @const The server encountered an unexpected condition which prevented it from fulfilling the request */
    public const INTERNAL_SERVER_ERROR = 500;
    /** @const The server does not support the functionality required to fulfill the request */
    public const NOT_IMPLEMENTED = 501;
    /** @const The server acted as a gateway and got an invalid response */
    public const BAD_GATEWAY = 502;
    /** @const The server is currently unable to handle the request due to a temporary overloading/maintenance */
    public const SERVICE_UNAVAILABLE = 503;
    /** @const The server acted as a gateway and timed out */
    public const GATEWAY_TIMEOUT = 504;
    /** @const The HTTP version in the request isn't supported */
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    /** @const Transparent content negotiation for the request results in a circular reference */
    public const VARIANT_ALSO_NEGOTIATES = 506;
    /** @const The server is unable to store the representation needed to complete the request */
    public const INSUFFICIENT_STORAGE = 507;
    /** @const The server detected an infinite loop while processing the request */
    public const LOOP_DETECTED = 508;
    /** @const Further extensions to the request are required for the server to fulfil it */
    public const NOT_EXTENDED = 510;
    /** @const The client needs to authenticate to gain network access */
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;
    /** @var array<int, string> Maps HTTP status codes to their default reason phrases */
    private static array $defaultReasonPhrases = [
        self::CONTINUE => 'Continue',
        self::SWITCHING_PROTOCOL => 'Switching Protocol',
        self::OK => 'OK',
        self::CREATED => 'Created',
        self::ACCEPTED => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::NO_CONTENT => 'No Content',
        self::RESET_CONTENT => 'Reset Content',
        self::PARTIAL_CONTENT => 'Partial Content',
        self::MULTIPLE_CHOICE => 'Multiple Choice',
        self::MOVED_PERMANENTLY => 'Moved Permanently',
        self::FOUND => 'Found',
        self::SEE_OTHER => 'See Other',
        self::NOT_MODIFIED => 'Not Modified',
        self::USE_PROXY => 'Use Proxy',
        self::TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::PERMANENT_REDIRECT => 'Permanent Redirect',
        self::BAD_REQUEST => 'Bad Request',
        self::UNAUTHORIZED => 'Unauthorized',
        self::PAYMENT_REQUIRED => 'Payment Required',
        self::FORBIDDEN => 'Forbidden',
        self::NOT_FOUND => 'Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::NOT_ACCEPTABLE => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT => 'Request Timeout',
        self::CONFLICT => 'Conflict',
        self::GONE => 'Gone',
        self::LENGTH_REQUIRED => 'Length Required',
        self::PRECONDITION_FAILED => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::EXPECTATION_FAILED => 'Expectation Failed',
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::NOT_IMPLEMENTED => 'Not Implemented',
        self::BAD_GATEWAY => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported'
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
