<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Validation;

use Aphiria\ContentNegotiation\ILanguageMatcher;
use Aphiria\Net\Http\IRequest;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\ValidationException;

/**
 * Defines the request body validator that uses the Aphiria validation library
 */
final class RequestBodyValidator implements IRequestBodyValidator
{
    /** @var array<string, string|null> The memoized matched languages per request */
    private array $memoizedMatchedLanguagesByRequest = [];

    /**
     * @param IValidator $validator The validator that will actually perform the validation
     * @param IErrorMessageInterpolator|null $errorMessageInterpolator The interpolator of error messages, or null if not using one
     * @param ILanguageMatcher|null $languageMatcher The language matcher to use, or null if not using one
     */
    public function __construct(
        private readonly IValidator $validator,
        private readonly ?IErrorMessageInterpolator $errorMessageInterpolator = null,
        private readonly ?ILanguageMatcher $languageMatcher = null
    ) {
    }

    /**
     * @inheritdoc
     */
    public function validate(IRequest $request, mixed $body): void
    {
        // Set up the locale for the error messages, if possible
        if ($this->languageMatcher !== null) {
            $memoizationKey = \spl_object_hash($request);

            if (!\array_key_exists($memoizationKey, $this->memoizedMatchedLanguagesByRequest)) {
                $language = $this->languageMatcher->getBestLanguageMatch($request);
                $this->memoizedMatchedLanguagesByRequest[$memoizationKey] = $language;

                if ($this->errorMessageInterpolator !== null && $language !== null) {
                    $this->errorMessageInterpolator->defaultLocale = $language;
                }
            }
        }

        try {
            if (\is_array($body)) {
                /** @var object $bodyPart */
                foreach ($body as $bodyPart) {
                    $this->validator->validateObject($bodyPart);
                }
            } elseif (\is_object($body)) {
                $this->validator->validateObject($body);
            }
        } catch (ValidationException $ex) {
            $errors = [];

            foreach ($ex->violations as $violation) {
                $errors[] = $violation->errorMessage;
            }

            throw new InvalidRequestBodyException($errors, 'Invalid request body', 0, $ex);
        }
    }
}
