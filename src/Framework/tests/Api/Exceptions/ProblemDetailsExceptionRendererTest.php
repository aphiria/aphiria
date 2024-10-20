<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Exceptions;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\Response;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProblemDetailsExceptionRendererTest extends TestCase
{
    private IRequest&MockObject $request;
    private IResponseFactory&MockObject $responseFactory;
    private IResponseWriter&MockObject $responseWriter;

    protected function setUp(): void
    {
        $this->request = $this->createMock(IRequest::class);
        $this->responseFactory = $this->createMock(IResponseFactory::class);
        $this->responseWriter = $this->createMock(IResponseWriter::class);
    }

    /**
     * @return list<list<mixed>> The list of problem details property names, raw values, and expected values
     */
    public static function getMapValues(): array
    {
        return [
            ['type', 'foo', 'foo'],
            ['type', fn (Exception $ex): string => 'foo', 'foo'],
            ['title', 'foo', 'foo'],
            ['title', fn (Exception $ex): string => 'foo', 'foo'],
            ['detail', 'foo', 'foo'],
            ['detail', fn (Exception $ex): string => 'foo', 'foo'],
            ['status', 404, 404],
            ['status', fn (Exception $ex): int => 404, 404],
            ['status', fn (Exception $ex): HttpStatusCode => HttpStatusCode::NotFound, 404],
            ['instance', 'foo', 'foo'],
            ['instance', fn (Exception $ex): string => 'foo', 'foo'],
            ['extensions', ['foo' => 'bar'], ['foo' => 'bar']],
            ['extensions', fn (Exception $ex): array => ['foo' => 'bar'], ['foo' => 'bar']]
        ];
    }

    public function testCreatingResponseForExceptionWithoutCustomMappingCreatesProblemDetailsWith500Status(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{status: int} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(HttpStatusCode::InternalServerError->value, $problemDetailsJson['status']);
        $this->assertSame(HttpStatusCode::InternalServerError, $response->statusCode);
    }

    public function testHavingRequestSetButAnExceptionGetsThrownCausesGenericResponse(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(true, true);
        $exceptionRenderer->mapExceptionToProblemDetails(
            Exception::class,
            function (Exception $ex): string {
                throw new Exception();
            }
        );
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IResponse $response) {
                return $response->statusCode === HttpStatusCode::InternalServerError
                    && $response->body === null
                    && $response->headers->count() === 0;
            }));
        $exceptionRenderer->render(new Exception());
    }

    public function testHavingRequestSetButNoCustomMappingCreatesProblemDetailsResponse(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(true, true);
        $expectedResponse = new Response(HttpStatusCode::InternalServerError);
        $this->responseFactory->expects($this->once())
            ->method('createResponse')
            ->with($this->request, HttpStatusCode::InternalServerError->value, null, new ProblemDetails('https://tools.ietf.org/html/rfc7231#section-6.6.1', null, null, HttpStatusCode::InternalServerError))
            ->willReturn($expectedResponse);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $exceptionRenderer->render(new Exception());
    }

    public function testHavingRequestSetWithACustomMappingCreatesResponseFromMapping(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(true, true);
        $exceptionRenderer->mapExceptionToProblemDetails(
            InvalidArgumentException::class,
            'type',
            'title',
            'detail',
            404,
            'instance',
            ['foo' => 'bar']
        );
        $expectedResponse = new Response(404);
        $this->responseFactory->expects($this->once())
            ->method('createResponse')
            ->with($this->request, 404, null, new ProblemDetails('type', 'title', 'detail', 404, 'instance', ['foo' => 'bar']))
            ->willReturn($expectedResponse);
        $actualResponse = $exceptionRenderer->createResponse(new InvalidArgumentException());
        // Intentionally not using assertSame() because the problem details mutator clones the response
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testMappingExceptionWithCustomStatusWithNoRfcUriDefaultsToNull(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, null, null, null, 100);
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{type: string|null} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertNull($problemDetailsJson['type']);
    }

    public function testMappingExceptionWithCustomStatusWithRfcUriDefaultsToThatUri(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, null, null, null, 404);
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{type: string} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://tools.ietf.org/html/rfc7231#section-6.5.4', $problemDetailsJson['type']);
    }

    public function testMappingExceptionWithoutCustomInstanceDetailDefaultsToNull(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class);
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{instance: string|null} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertNull($problemDetailsJson['instance']);
    }

    public function testMappingExceptionWithoutCustomStatusDefaultsTo500(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, 'foo');
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{status: int} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(HttpStatusCode::InternalServerError, $response->statusCode);
        $this->assertSame(HttpStatusCode::InternalServerError->value, $problemDetailsJson['status']);
    }

    public function testMappingExceptionWithoutCustomTitleDefaultsToExceptionMessage(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class);
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException('foo'));
        /** @var array{title: string} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('foo', $problemDetailsJson['title']);
    }

    public function testMappingExceptionWithoutCustomTypeDefaultsTo500RfcUri(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class);
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{type: string} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://tools.ietf.org/html/rfc7231#section-6.6.1', $problemDetailsJson['type']);
    }

    public function testMappingExceptionWithoutCustomTypeDetailDefaultsToNull(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class);
        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{detail: string|null} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertNull($problemDetailsJson['detail']);
    }

    /**
     * @param string $propertyName The name of the problem details property that is being set
     * @param mixed $rawValue The raw value passed into the map method
     * @param mixed $expectedValue The expected property value
     */
    #[DataProvider('getMapValues')]
    public function testMappingProblemDetailsPropertiesWithCallbacksAndValuesSetsProperties(
        string $propertyName,
        mixed $rawValue,
        mixed $expectedValue
    ): void {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);

        switch ($propertyName) {
            case 'type':
                $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, $rawValue);
                break;
            case 'title':
                $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, title: $rawValue);
                break;
            case 'detail':
                $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, detail: $rawValue);
                break;
            case 'status':
                $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, status: $rawValue);
                break;
            case 'instance':
                $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, instance: $rawValue);
                break;
            case 'extensions':
                $exceptionRenderer->mapExceptionToProblemDetails(InvalidArgumentException::class, extensions: $rawValue);
                break;
        }

        $response = $exceptionRenderer->createResponse(new InvalidArgumentException());
        /** @var array{type: string, title: string, detail: string, status: int, instance: string, extensions: array} $problemDetailsJson */
        $problemDetailsJson = \json_decode((string)$response->body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($expectedValue, $problemDetailsJson[$propertyName]);
    }

    public function testNotHavingRequestSetCreatesProblemDetailsResponse(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false);
        $actualResponse = $exceptionRenderer->createResponse(new Exception('foo'));
        $this->assertSame(HttpStatusCode::InternalServerError, $actualResponse->statusCode);
        $this->assertSame('application/problem+json', $actualResponse->headers->getFirst('Content-Type'));
        // In this test, we're not using the custom problem details Symfony normalizer, which means "extensions" will appear as a property in the JSON
        $this->assertSame('{"status":500,"type":"https:\/\/tools.ietf.org\/html\/rfc7231#section-6.6.1","title":"foo","detail":null,"instance":null,"extensions":null}', (string)$actualResponse->body);
    }

    /**
     * Creates an exception renderer
     *
     * @param bool $setRequest Whether or not to set the request
     * @param bool $setResponseFactory Whether or not to set the response factory
     * @return ProblemDetailsExceptionRenderer The exception renderer
     */
    private function createExceptionRenderer(
        bool $setRequest,
        bool $setResponseFactory
    ): ProblemDetailsExceptionRenderer {
        $renderer = new ProblemDetailsExceptionRenderer(
            $setResponseFactory ? $this->responseFactory : null,
            $this->responseWriter
        );

        if ($setRequest) {
            $renderer->request = $this->request;
        }

        return $renderer;
    }
}
