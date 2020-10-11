<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Validation;

use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Api\Validation\RequestBodyValidator;
use Aphiria\ContentNegotiation\ILanguageMatcher;
use Aphiria\Net\Http\IRequest;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ConstraintViolation;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestBodyValidatorTest extends TestCase
{
    private IRequest|MockObject $request;
    private IValidator|MockObject $validator;
    private IErrorMessageInterpolator|MockObject $errorMessageInterpolator;
    private ILanguageMatcher|MockObject $languageMatcher;
    private RequestBodyValidator $requestBodyValidator;

    protected function setUp(): void
    {
        $this->request = $this->createMock(IRequest::class);
        $this->validator = $this->createMock(IValidator::class);
        $this->errorMessageInterpolator = $this->createMock(IErrorMessageInterpolator::class);
        $this->languageMatcher = $this->createMock(ILanguageMatcher::class);
        $this->requestBodyValidator = new RequestBodyValidator(
            $this->validator,
            $this->errorMessageInterpolator,
            $this->languageMatcher
        );
    }

    public function testValidatingArrayOfObjectsValidatesEachOne(): void
    {
        $bodyParts = [new class() {
        }, new class() {
        }];
        $this->validator->method('validateObject')
            ->withConsecutive([$bodyParts[0]], [$bodyParts[1]]);
        $this->requestBodyValidator->validate($this->request, $bodyParts);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testValidatingDoesNotSetLocaleOnErrorMessageInterpolatorIfNoLanguageMatcherFound(): void
    {
        $this->languageMatcher->expects($this->once())
            ->method('getBestLanguageMatch')
            ->with($this->request)
            ->willReturn(null);
        $this->errorMessageInterpolator->expects($this->never())
            ->method('setDefaultLocale');
        $this->requestBodyValidator->validate($this->request, $this);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testValidatingInvalidBodyPopulatesExceptionErrorsWithErrorsFromViolations(): void
    {
        $violations = [
            new ConstraintViolation(
                'error1',
                $this->createMock(IConstraint::class),
                $this,
                $this
            ),
            new ConstraintViolation(
                'error2',
                $this->createMock(IConstraint::class),
                $this,
                $this
            )
        ];
        $expectedException = new ValidationException($violations);
        $this->validator->expects($this->once())
            ->method('validateObject')
            ->with($this)
            ->willThrowException($expectedException);

        try {
            $this->requestBodyValidator->validate($this->request, $this);
            $this->fail('Failed to assert that ' . InvalidRequestBodyException::class . ' is thrown');
        } catch (InvalidRequestBodyException $ex) {
            $this->assertEquals(['error1', 'error2'], $ex->getErrors());
            $this->assertSame('Invalid request body', $ex->getMessage());
            // Dummy assertion
            $this->assertTrue(true);
        }
    }

    public function testValidatingSetsLocaleOnErrorMessageInterpolatorOnlyOnce(): void
    {
        $this->languageMatcher->expects($this->once())
            ->method('getBestLanguageMatch')
            ->with($this->request)
            ->willReturn('en-US');
        $this->errorMessageInterpolator->expects($this->once())
            ->method('setDefaultLocale')
            ->with('en-US');
        // Double-validating this body should be sufficient to test this
        $this->requestBodyValidator->validate($this->request, $this);
        $this->requestBodyValidator->validate($this->request, $this);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testValidatingSetsLocaleOnErrorMessageInterpolatorIfLanguageMatcherFoundOne(): void
    {
        $this->languageMatcher->expects($this->once())
            ->method('getBestLanguageMatch')
            ->with($this->request)
            ->willReturn('en-US');
        $this->errorMessageInterpolator->expects($this->once())
            ->method('setDefaultLocale')
            ->with('en-US');
        $this->requestBodyValidator->validate($this->request, $this);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testValidatingScalarValueDoesNotThrowException(): void
    {
        $this->requestBodyValidator->validate($this->request, 1);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testValidatingValidBodyDoesNotThrowException(): void
    {
        $this->validator->expects($this->once())
            ->method('validateObject')
            ->with($this);
        $this->requestBodyValidator->validate($this->request, $this);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
