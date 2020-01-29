<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Validation;

use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Api\Validation\RequestBodyValidator;
use Aphiria\Net\Http\ContentNegotiation\ILanguageMatcher;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ConstraintViolation;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\ValidationContext;
use Aphiria\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the request body validator
 */
class RequestBodyValidatorTest extends TestCase
{
    /** @var IHttpRequestMessage|MockObject */
    private IHttpRequestMessage $request;
    /** @var IValidator|MockObject */
    private IValidator $validator;
    /** @var IErrorMessageInterpolator|MockObject */
    private $errorMessageInterpolator;
    /** @var ILanguageMatcher|MockObject */
    private ILanguageMatcher $languageMatcher;
    private RequestBodyValidator $requestBodyValidator;

    protected function setUp(): void
    {
        $this->request = $this->createMock(IHttpRequestMessage::class);
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
        $bodyParts = [new class() {}, new class() {}];
        $this->validator->expects($this->at(0))
            ->method('validateObject')
            ->with($bodyParts[0]);
        $this->validator->expects($this->at(1))
            ->method('validateObject')
            ->with($bodyParts[1]);
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
        $expectedContext = new ValidationContext($this);
        $expectedContext->addConstraintViolation(new ConstraintViolation(
            'error1',
            $this->createMock(IConstraint::class),
            $this,
            $this
        ));
        $expectedContext->addConstraintViolation(new ConstraintViolation(
            'error2',
            $this->createMock(IConstraint::class),
            $this,
            $this
        ));
        $expectedException = new ValidationException($expectedContext);
        $this->validator->expects($this->once())
            ->method('validateObject')
            ->with($this, $this->callback(fn () => true))
            ->willThrowException($expectedException);

        try {
            $this->requestBodyValidator->validate($this->request, $this);
            $this->fail('Failed to assert that ' . InvalidRequestBodyException::class . ' is thrown');
        } catch (InvalidRequestBodyException $ex) {
            $this->assertEquals(['error1', 'error2'], $ex->getErrors());
            $this->assertEquals('Invalid request body', $ex->getMessage());
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
            ->with($this, $this->callback(fn () => true));
        $this->requestBodyValidator->validate($this->request, $this);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
