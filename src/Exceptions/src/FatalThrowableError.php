<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use ErrorException;
use ParseError;
use Throwable;
use TypeError;

/**
 * Defines a wrapper for fatal throwable errors
 */
class FatalThrowableError extends ErrorException
{
    /**
     * @param Throwable $error The throwable that caused the error
     */
    public function __construct(Throwable $error)
    {
        if ($error instanceof TypeError) {
            $message = "Type error: {$error->getMessage()}";
            $severity = E_RECOVERABLE_ERROR;
        } elseif ($error instanceof ParseError) {
            $message = "Parse error: {$error->getMessage()}";
            $severity = E_PARSE;
        } else {
            $message = "Fatal error: {$error->getMessage()}";
            $severity = E_ERROR;
        }

        /** @psalm-suppress PossiblyInvalidArgument getCode() will always return an int - bug */
        parent::__construct($message, $error->getCode(), $severity, $error->getFile(), $error->getLine());
    }
}
