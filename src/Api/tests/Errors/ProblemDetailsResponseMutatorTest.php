<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Errors;

use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpResponseMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the problem details response mutator
 */
class ProblemDetailsResponseMutatorTest extends TestCase
{
    private ProblemDetailsResponseMutator $mutator;
    private HttpHeaders $headers;
    /** @var IHttpResponseMessage|MockObject */
    private IHttpResponseMessage $response;

    protected function setUp(): void
    {
        $this->mutator = new ProblemDetailsResponseMutator();
        $this->headers = new HttpHeaders();
        $this->response = $this->createMock(IHttpResponseMessage::class);
        $this->response->method('getHeaders')
            ->willReturn($this->headers);
    }

    public function testMutatingResponseDoesNotChangeParameterResponse(): void
    {
        $this->assertEquals($this->response, $this->mutator->mutateResponse($this->response));
        $this->assertNotSame($this->response, $this->mutator->mutateResponse($this->response));
    }

    public function testMutatingResponseWithAcceptableJsonContentTypesGetChangedToProblemDetailsJsonContentType(): void
    {
        $this->headers->add('Content-Type', 'application/json');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertEquals('application/problem+json', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
        $this->headers->add('Content-Type', 'text/json');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertEquals('application/problem+json', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
    }

    public function testMutatingResponseWithAcceptableXmlContentTypesGetChangedToProblemDetailsXmlContentType(): void
    {
        $this->headers->add('Content-Type', 'application/xml');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertEquals('application/problem+xml', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
        $this->headers->add('Content-Type', 'text/xml');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertEquals('application/problem+xml', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
    }

    public function testMutatingResponseWithNoContentTypeDoesNothing(): void
    {
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertEquals($this->response, $mutatedResponse);
        $contentType = null;
        $this->assertFalse($mutatedResponse->getHeaders()->tryGetFirst('Content-Type', $contentType));
        $this->assertNull($contentType);
    }

    public function testMutatingResponseWithNonJsonXmlContentTypeLeavesOriginalContentType(): void
    {
        $this->headers->add('Content-Type', 'foo/bar');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertEquals('foo/bar', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
    }
}
