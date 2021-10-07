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

/**
 * Defines the various HTTP status codes
 */
final class HttpStatusCode
{
    /** @const Continue */
    public const Continue = 100;
    /** @const Switching protocol */
    public const SwitchingProtocol = 101;
    /** @const Successful response */
    public const Ok = 200;
    /** @const Request has been fulfilled and a new resource has been created */
    public const Created = 201;
    /** @const The request has been accepted for processing, but processing hasn't completed */
    public const Accepted = 202;
    /** @const The response was collected from a copy */
    public const NonAuthoritativeInformation = 203;
    /** @const No content */
    public const NoContent = 204;
    /** @const After accomplishing request to tell user agent reset document view which sent the request */
    public const ResetContent = 205;
    /** @const The request contains partial content */
    public const PartialContent = 206;
    /** @const The request contains multiple status codes */
    public const MultiStatus = 207;
    /** @const The members of a DAV binding have been enumerated in a preceding part of the multi-status response */
    public const AlreadyReported = 208;
    /** @const The server has fulfilled a request for the resource, and the response represents one or more instance-manipulations */
    public const ImUsed = 226;
    /** @const Multiple choice redirect */
    public const MultipleChoice = 300;
    /** @const Moved permanently */
    public const MovedPermanently = 301;
    /** @const The URI has been changed temporarily */
    public const Found = 302;
    /** @const See other */
    public const SeeOther = 303;
    /** @const The response has not been modified */
    public const NotModified = 304;
    /** @const The response must be accept by a proxy */
    public const UseProxy = 305;
    /** @const A temporary redirect */
    public const TemporaryRedirect = 307;
    /** @const The request URI is now permanently at another URI */
    public const PermanentRedirect = 308;
    /** @const The request was bad */
    public const BadRequest = 400;
    /** @const The request requires authentication */
    public const Unauthorized = 401;
    /** @const Payment is required */
    public const PaymentRequired = 402;
    /** @const The server understood the request, but is refusing to fulfill it */
    public const Forbidden = 403;
    /** @const The server didn't find anything matching the request URI */
    public const NotFound = 404;
    /** @const The method is not allowed */
    public const MethodNotAllowed = 405;
    /** @const Cannot find content with the criteria from the user agent */
    public const NotAcceptable = 406;
    /** @const Authentication needs to be done via a proxy */
    public const ProxyAuthenticationRequired = 407;
    /** @const The request timed out */
    public const RequestTimeout = 408;
    /** @const There's a conflict with the state of the server */
    public const Conflict = 409;
    /** @const The content has been deleted from the server */
    public const Gone = 410;
    /** @const The The content-length header was required wasn't defined */
    public const LengthRequired = 411;
    /** @const Preconditions in the headers were not met */
    public const PreconditionFailed = 412;
    /** @const The request entity was too large */
    public const RequestEntityTooLarge = 413;
    /** @const The request URI was too long */
    public const UriTooLong = 414;
    /** @const The request media format wasn't supported */
    public const UnsupportedMediaType = 415;
    /** @const The range header cannot be fulfilled */
    public const RequestedRangeNotSatisfiable = 416;
    /** @const The expected header cannot be met */
    public const ExpectationFailed = 417;
    /** @const The request is a teapot, and totally legit */
    public const Teapot = 418;
    /** @const The request was directed at a server that is not able to produce a response */
    public const MisdirectedRequest = 421;
    /** @const The request was well-formed, but was unable to be followed due to semantic errors */
    public const UnprocessableEntity = 422;
    /** @const The resource that is being accessed is locked */
    public const Locked = 423;
    /** @const The request failed because it depended on another request that failed */
    public const FailedDependency = 424;
    /** @const The client should switch to a different protocol */
    public const UpgradeRequired = 426;
    /** @const The origin server requires the request to be conditional */
    public const PreconditionRequired = 428;
    /** @const The user has sent too many requests in a given amount of time */
    public const TooManyRequests = 429;
    /** @const The server is unwilling to process the request the header fields are too large */
    public const RequestHeaderFieldsTooLarge = 431;
    /** @const The server has received a legal demand to deny access to a resource */
    public const UnavailableForLegalReasons = 451;
    /** @const The server encountered an unexpected condition which prevented it from fulfilling the request */
    public const InternalServerError = 500;
    /** @const The server does not support the functionality required to fulfill the request */
    public const NotImplemented = 501;
    /** @const The server acted as a gateway and got an invalid response */
    public const BadGateway = 502;
    /** @const The server is currently unable to handle the request due to a temporary overloading/maintenance */
    public const ServiceUnavailable = 503;
    /** @const The server acted as a gateway and timed out */
    public const GatewayTimeout = 504;
    /** @const The HTTP version in the request isn't supported */
    public const HttpVersionNotSupported = 505;
    /** @const Transparent content negotiation for the request results in a circular reference */
    public const VariantAlsoNegotiates = 506;
    /** @const The server is unable to store the representation needed to complete the request */
    public const InsufficientStorage = 507;
    /** @const The server detected an infinite loop while processing the request */
    public const LoopDetected = 508;
    /** @const Further extensions to the request are required for the server to fulfil it */
    public const NotExtended = 510;
    /** @const The client needs to authenticate to gain network access */
    public const NetworkAuthenticationRequired = 511;
    /** @var array<int, string> Maps HTTP status codes to their default reason phrases */
    private static array $defaultReasonPhrases = [
        self::Continue => 'Continue',
        self::SwitchingProtocol => 'Switching Protocol',
        self::Ok => 'OK',
        self::Created => 'Created',
        self::Accepted => 'Accepted',
        self::NonAuthoritativeInformation => 'Non-Authoritative Information',
        self::NoContent => 'No Content',
        self::ResetContent => 'Reset Content',
        self::PartialContent => 'Partial Content',
        self::MultipleChoice => 'Multiple Choice',
        self::MovedPermanently => 'Moved Permanently',
        self::Found => 'Found',
        self::SeeOther => 'See Other',
        self::NotModified => 'Not Modified',
        self::UseProxy => 'Use Proxy',
        self::TemporaryRedirect => 'Temporary Redirect',
        self::PermanentRedirect => 'Permanent Redirect',
        self::BadRequest => 'Bad Request',
        self::Unauthorized => 'Unauthorized',
        self::PaymentRequired => 'Payment Required',
        self::Forbidden => 'Forbidden',
        self::NotFound => 'Not Found',
        self::MethodNotAllowed => 'Method Not Allowed',
        self::NotAcceptable => 'Not Acceptable',
        self::ProxyAuthenticationRequired => 'Proxy Authentication Required',
        self::RequestTimeout => 'Request Timeout',
        self::Conflict => 'Conflict',
        self::Gone => 'Gone',
        self::LengthRequired => 'Length Required',
        self::PreconditionFailed => 'Precondition Failed',
        self::RequestEntityTooLarge => 'Request Entity Too Large',
        self::UnsupportedMediaType => 'Unsupported Media Type',
        self::RequestedRangeNotSatisfiable => 'Requested Range Not Satisfiable',
        self::ExpectationFailed => 'Expectation Failed',
        self::InternalServerError => 'Internal Server Error',
        self::NotImplemented => 'Not Implemented',
        self::BadGateway => 'Bad Gateway',
        self::ServiceUnavailable => 'Service Unavailable',
        self::GatewayTimeout => 'Gateway Timeout',
        self::HttpVersionNotSupported => 'HTTP Version Not Supported'
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
