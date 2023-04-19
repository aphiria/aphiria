<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the various HTTP status codes
 */
enum HttpStatusCode: int
{
    /** The request has been accepted for processing, but processing hasn't completed */
    case Accepted = 202;
    /** The members of a DAV binding have been enumerated in a preceding part of the multi-status response */
    case AlreadyReported = 208;
    /** The server acted as a gateway and got an invalid response */
    case BadGateway = 502;
    /** The request was bad */
    case BadRequest = 400;
    /** There's a conflict with the state of the server */
    case Conflict = 409;
    /** Continue */
    case Continue = 100;
    /** Request has been fulfilled and a new resource has been created */
    case Created = 201;
    /** The expected header cannot be met */
    case ExpectationFailed = 417;
    /** The request failed because it depended on another request that failed */
    case FailedDependency = 424;
    /** The server understood the request, but is refusing to fulfill it */
    case Forbidden = 403;
    /** The URI has been changed temporarily */
    case Found = 302;
    /** The server acted as a gateway and timed out */
    case GatewayTimeout = 504;
    /** The content has been deleted from the server */
    case Gone = 410;
    /** The HTTP version in the request isn't supported */
    case HttpVersionNotSupported = 505;
    /** The server has fulfilled a request for the resource, and the response represents one or more instance-manipulations */
    case ImUsed = 226;
    /** The server is unable to store the representation needed to complete the request */
    case InsufficientStorage = 507;
    /** The server encountered an unexpected condition which prevented it from fulfilling the request */
    case InternalServerError = 500;
    /** The The content-length header was required wasn't defined */
    case LengthRequired = 411;
    /** The resource that is being accessed is locked */
    case Locked = 423;
    /** The server detected an infinite loop while processing the request */
    case LoopDetected = 508;
    /** The method is not allowed */
    case MethodNotAllowed = 405;
    /** The request was directed at a server that is not able to produce a response */
    case MisdirectedRequest = 421;
    /** Moved permanently */
    case MovedPermanently = 301;
    /** Multiple choice redirect */
    case MultipleChoice = 300;
    /** The request contains multiple status codes */
    case MultiStatus = 207;
    /** The client needs to authenticate to gain network access */
    case NetworkAuthenticationRequired = 511;
    /** No content */
    case NoContent = 204;
    /** The response was collected from a copy */
    case NonAuthoritativeInformation = 203;
    /** Cannot find content with the criteria from the user agent */
    case NotAcceptable = 406;
    /** Further extensions to the request are required for the server to fulfil it */
    case NotExtended = 510;
    /** The server didn't find anything matching the request URI */
    case NotFound = 404;
    /** The server does not support the functionality required to fulfill the request */
    case NotImplemented = 501;
    /** The response has not been modified */
    case NotModified = 304;
    /** Successful response */
    case Ok = 200;
    /** The request contains partial content */
    case PartialContent = 206;
    /** Payment is required */
    case PaymentRequired = 402;
    /** The request URI is now permanently at another URI */
    case PermanentRedirect = 308;
    /** Preconditions in the headers were not met */
    case PreconditionFailed = 412;
    /** The origin server requires the request to be conditional */
    case PreconditionRequired = 428;
    /** Authentication needs to be done via a proxy */
    case ProxyAuthenticationRequired = 407;
    /** The range header cannot be fulfilled */
    case RequestedRangeNotSatisfiable = 416;
    /** The request entity was too large */
    case RequestEntityTooLarge = 413;
    /** The server is unwilling to process the request the header fields are too large */
    case RequestHeaderFieldsTooLarge = 431;
    /** The request timed out */
    case RequestTimeout = 408;
    /** After accomplishing request to tell user agent reset document view which sent the request */
    case ResetContent = 205;
    /** See other */
    case SeeOther = 303;
    /** The server is currently unable to handle the request due to a temporary overloading/maintenance */
    case ServiceUnavailable = 503;
    /** Switching protocol */
    case SwitchingProtocol = 101;
    /** The request is a teapot, and totally legit */
    case Teapot = 418;
    /** A temporary redirect */
    case TemporaryRedirect = 307;
    /** The user has sent too many requests in a given amount of time */
    case TooManyRequests = 429;
    /** The request requires authentication */
    case Unauthorized = 401;
    /** The server has received a legal demand to deny access to a resource */
    case UnavailableForLegalReasons = 451;
    /** The request was well-formed, but was unable to be followed due to semantic errors */
    case UnprocessableEntity = 422;
    /** The request media format wasn't supported */
    case UnsupportedMediaType = 415;
    /** The client should switch to a different protocol */
    case UpgradeRequired = 426;
    /** The request URI was too long */
    case UriTooLong = 414;
    /** The response must be accept by a proxy */
    case UseProxy = 305;
    /** Transparent content negotiation for the request results in a circular reference */
    case VariantAlsoNegotiates = 506;

    /**
     * Gets the default reason phrase for a status code
     *
     * @param HttpStatusCode|int $statusCode The status code whose reason phrase we want
     * @return string|null The default reason code if one exists, otherwise null
     */
    public static function getDefaultReasonPhrase(HttpStatusCode|int $statusCode): ?string
    {
        if (\is_int($statusCode)) {
            $statusCode = HttpStatusCode::tryFrom($statusCode);
        }

        if ($statusCode === null) {
            return null;
        }

        return match ($statusCode) {
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
            self::HttpVersionNotSupported => 'HTTP Version Not Supported',
            default => null
        };
    }
}
