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

use Aphiria\Net\Http\ContentNegotiation\ILanguageMatcher;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\ValidationException;

/**
 * Defines the request body validator that uses the Aphiria validation library
 */
final class RequestBodyValidator implements IRequestBodyValidator
{
    /** @var IValidator The validator that will actually perform the validation */
    private IValidator $validator;
    /** @var IErrorMessageInterpolator|null The interpolator of error messages, or null if not using one */
    private ?IErrorMessageInterpolator $errorMessageInterpolator;
    /** @var ILanguageMatcher|null The language matcher to use, or null if not using one */
    private ?ILanguageMatcher $languageMatcher;
    /** @var string[] The memoized matched languages per request */
    private array $memoizedMatchedLanguagesByRequest = [];

    /**
     * @param IValidator $validator The validator that will actually perform the validation
     * @param IErrorMessageInterpolator|null $errorMessageInterpolator The interpolator of error messages, or null if not using one
     * @param ILanguageMatcher|null $languageMatcher The language matcher to use, or null if not using one
     */
    public function __construct(
        IValidator $validator,
        IErrorMessageInterpolator $errorMessageInterpolator = null,
        ILanguageMatcher $languageMatcher = null
    ) {
        $this->validator = $validator;
        $this->errorMessageInterpolator = $errorMessageInterpolator;
        $this->languageMatcher = $languageMatcher;
    }

    /**
     * @inheritdoc
     */
    public function validate(IHttpRequestMessage $request, $body): void
    {
        // Set up the locale for the error messages, if possible
        if ($this->languageMatcher !== null) {
            $memoizationKey = \spl_object_hash($request);

            if (!array_key_exists($memoizationKey, $this->memoizedMatchedLanguagesByRequest)) {
                $language = $this->languageMatcher->getBestLanguageMatch($request);
                $this->memoizedMatchedLanguagesByRequest[$memoizationKey] = $language;

                if ($language !== null) {
                    $this->errorMessageInterpolator->setDefaultLocale($language);
                }
            }
        }

        try {
            if (\is_array($body)) {
                foreach ($body as $bodyPart) {
                    $this->validator->validateObject($bodyPart);
                }
            } elseif (\is_object($body)) {
                $this->validator->validateObject($body);
            }
        } catch (ValidationException $ex) {
            throw new InvalidRequestBodyException(
                $ex->getValidationContext()->getErrorMessages(),
                'Invalid request body',
                0,
                $ex
            );
        }
    }
}
