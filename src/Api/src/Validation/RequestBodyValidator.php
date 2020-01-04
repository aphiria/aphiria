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

use Aphiria\Validation\ErrorMessages\IErrorMessageFormatter;
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
    /** @var IErrorMessageFormatter The compiler of error messages */
    private IErrorMessageFormatter $errorMessageCompiler;

    /**
     * @inheritdoc
     */
    public function validate($body): void
    {
        // There isn't any way to validate a scalar value without a list of constraints.  So, just say it's valid.
        if (!\is_object($body)) {
            return;
        }

        try {
            $this->validator->validateObject($body, new ValidationContext($body));
        } catch (ValidationException $ex) {
            $compiledErrorMessages = [];

            foreach ($ex->getValidationContext()->getConstraintViolations() as $violation) {
                $failedConstraint = $violation->getConstraint();
                // TODO: How do I get the accepted locale from content negotiation?  If I perform content negotiation here, what type do I specify?  Technically, this class is throwing an exception, not creating a response.  So, knowing the type of the body is poor encapsulation.
                $compiledErrorMessages[] = $this->errorMessageCompiler->format(
                    $failedConstraint->getErrorMessageId(),
                    $failedConstraint->getErrorMessagePlaceholders()
                );
            }

            throw new InvalidRequestBodyException($compiledErrorMessages, 'Invalid request body', 0, $ex);
        }
    }
}
