<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\HttpStatusCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HttpStatusCodeTest extends TestCase
{
    public static function getDefaultReasonPhrases(): array
    {
        return [
            [HttpStatusCode::Continue, 'Continue'],
            [HttpStatusCode::SwitchingProtocol, 'Switching Protocol'],
            [HttpStatusCode::Ok, 'OK'],
            [HttpStatusCode::Created, 'Created'],
            [HttpStatusCode::Accepted, 'Accepted'],
            [HttpStatusCode::NonAuthoritativeInformation, 'Non-Authoritative Information'],
            [HttpStatusCode::NoContent, 'No Content'],
            [HttpStatusCode::ResetContent, 'Reset Content'],
            [HttpStatusCode::PartialContent, 'Partial Content'],
            [HttpStatusCode::MultipleChoice, 'Multiple Choice'],
            [HttpStatusCode::MovedPermanently, 'Moved Permanently'],
            [HttpStatusCode::Found, 'Found'],
            [HttpStatusCode::SeeOther, 'See Other'],
            [HttpStatusCode::NotModified, 'Not Modified'],
            [HttpStatusCode::UseProxy, 'Use Proxy'],
            [HttpStatusCode::TemporaryRedirect, 'Temporary Redirect'],
            [HttpStatusCode::PermanentRedirect, 'Permanent Redirect'],
            [HttpStatusCode::BadRequest, 'Bad Request'],
            [HttpStatusCode::Unauthorized, 'Unauthorized'],
            [HttpStatusCode::PaymentRequired, 'Payment Required'],
            [HttpStatusCode::Forbidden, 'Forbidden'],
            [HttpStatusCode::NotFound, 'Not Found'],
            [HttpStatusCode::MethodNotAllowed, 'Method Not Allowed'],
            [HttpStatusCode::NotAcceptable, 'Not Acceptable'],
            [HttpStatusCode::ProxyAuthenticationRequired, 'Proxy Authentication Required'],
            [HttpStatusCode::RequestTimeout, 'Request Timeout'],
            [HttpStatusCode::Conflict, 'Conflict'],
            [HttpStatusCode::Gone, 'Gone'],
            [HttpStatusCode::LengthRequired, 'Length Required'],
            [HttpStatusCode::PreconditionFailed, 'Precondition Failed'],
            [HttpStatusCode::RequestEntityTooLarge, 'Request Entity Too Large'],
            [HttpStatusCode::UnsupportedMediaType, 'Unsupported Media Type'],
            [HttpStatusCode::RequestedRangeNotSatisfiable, 'Requested Range Not Satisfiable'],
            [HttpStatusCode::ExpectationFailed, 'Expectation Failed'],
            [HttpStatusCode::InternalServerError, 'Internal Server Error'],
            [HttpStatusCode::NotImplemented, 'Not Implemented'],
            [HttpStatusCode::BadGateway, 'Bad Gateway'],
            [HttpStatusCode::ServiceUnavailable, 'Service Unavailable'],
            [HttpStatusCode::GatewayTimeout, 'Gateway Timeout'],
            [HttpStatusCode::HttpVersionNotSupported, 'HTTP Version Not Supported']
        ];
    }

    /**
     * @param HttpStatusCode $statusCode The status code to test
     * @param string $expectedDefaultReasonPhrase The expected default reason phrase
     */
    #[DataProvider('getDefaultReasonPhrases')]
    public function testDefaultReasonPhrasesAreCorrect(HttpStatusCode $statusCode, string $expectedDefaultReasonPhrase): void
    {
        $this->assertSame($expectedDefaultReasonPhrase, HttpStatusCode::getDefaultReasonPhrase($statusCode));
    }

    public function testExistingStatusCodeAsEnumReturnsDefaultStatusText(): void
    {
        $this->assertSame('OK', HttpStatusCode::getDefaultReasonPhrase(HttpStatusCode::Ok));
    }

    public function testExistingStatusCodeAsIntReturnsDefaultStatusText(): void
    {
        $this->assertSame('OK', HttpStatusCode::getDefaultReasonPhrase(200));
    }

    public function testNonExistentStatusCodeReturnsNullStatusText(): void
    {
        $this->assertNull(HttpStatusCode::getDefaultReasonPhrase(-1));
    }
}
