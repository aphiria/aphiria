<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Errors;

use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProblemDetailsResponseMutatorTest extends TestCase
{
    private ProblemDetailsResponseMutator $mutator;
    private Headers $headers;
    /** @var IResponse|MockObject */
    private IResponse $response;

    protected function setUp(): void
    {
        $this->mutator = new ProblemDetailsResponseMutator();
        $this->headers = new Headers();
        $this->response = $this->createMock(IResponse::class);
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
        $this->assertSame('application/problem+json', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
        $this->headers->add('Content-Type', 'text/json');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertSame('application/problem+json', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
    }

    public function testMutatingResponseWithAcceptableXmlContentTypesGetChangedToProblemDetailsXmlContentType(): void
    {
        $this->headers->add('Content-Type', 'application/xml');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertSame('application/problem+xml', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
        $this->headers->add('Content-Type', 'text/xml');
        $mutatedResponse = $this->mutator->mutateResponse($this->response);
        $this->assertSame('application/problem+xml', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
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
        $this->assertSame('foo/bar', $mutatedResponse->getHeaders()->getFirst('Content-Type'));
    }
}
