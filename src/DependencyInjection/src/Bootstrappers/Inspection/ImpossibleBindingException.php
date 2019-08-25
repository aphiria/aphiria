<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Bootstrappers\Inspection;

use Exception;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Throwable;

/**
 * Defines an exception that is thrown when bindings are impossible because they're not in any bootstrapper
 */
final class ImpossibleBindingException extends Exception
{
    /**
     * @inheritdoc
     * @param Bootstrapper[] $failedInterfacesToBootstrappers The mapping of failed interfaces to bootstrappers
     */
    public function __construct(array $failedInterfacesToBootstrappers, int $code = 0, Throwable $previous = null)
    {
        $message = 'Impossible to resolve following interfaces: ';

        foreach ($failedInterfacesToBootstrappers as $failedInterface => $failedBootstrappers) {
            $message .= $failedInterface . ' (attempted to be resolved in ';

            foreach ($failedBootstrappers as $failedBootstrapper) {
                $message .= \get_class($failedBootstrapper) . ', ';
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
