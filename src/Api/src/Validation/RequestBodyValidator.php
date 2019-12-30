<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Validation;

use Aphiria\Validation\ErrorMessages\IErrorMessageCompiler;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\ValidationContext;
use Aphiria\Validation\ValidationException;

/**
 * Defines the request body validator that uses the Aphiria validation library
 */
final class RequestBodyValidator implements IRequestBodyValidator
{
    /** @var IValidator The validator that will actually perform the validation */
    private IValidator $validator;
    /** @var IErrorMessageCompiler The compiler of error messages */
    private IErrorMessageCompiler $errorMessageCompiler;

    /**
     * @inheritdoc
     */
    public function validate(object $body): void
    {
        try {
            $this->validator->validateObject($body, new ValidationContext($body));
        } catch (ValidationException $ex) {
            $compiledErrorMessages = [];

            foreach ($ex->getValidationContext()->getConstraintViolations() as $violation) {
                $failedConstraint = $violation->getConstraint();
                // TODO: How do I get the accepted locale from content negotiation?
                $compiledErrorMessages[] = $this->errorMessageCompiler->compile(
                    $failedConstraint->getErrorMessageId(),
                    $failedConstraint->getErrorMessagePlaceholders()
                );
            }

            throw new InvalidRequestBodyException($compiledErrorMessages, 'Invalid request body', 0, $ex);
        }
    }
}
