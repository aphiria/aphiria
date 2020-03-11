<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Inspection;

use Exception;
use Aphiria\DependencyInjection\Binders\Binder;
use Throwable;

/**
 * Defines an exception that is thrown when bindings are impossible because they're not in any binder
 */
final class ImpossibleBindingException extends Exception
{
    /**
     * @inheritdoc
     * @param Binder[] $failedInterfacesToBinders The mapping of failed interfaces to binders
     */
    public function __construct(array $failedInterfacesToBinders, int $code = 0, Throwable $previous = null)
    {
        $message = 'Impossible to resolve following interfaces: ';

        foreach ($failedInterfacesToBinders as $failedInterface => $failedBinders) {
            $message .= $failedInterface . ' (attempted to be resolved in ';

            foreach ($failedBinders as $failedBinder) {
                $message .= \get_class($failedBinder) . ', ';
            }

            // Remove the trailing ', '
            $message = substr($message, 0, -2);
            // Close the parenthesis
            $message .= '), ';
        }

        // Remove the trailing ','
        $message = substr($message, 0, -2);
        parent::__construct($message, $code, $previous);
    }
}
