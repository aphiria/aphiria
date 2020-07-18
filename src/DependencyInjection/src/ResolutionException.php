<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Exception;
use Throwable;

/**
 * Defines an exception that's thrown when a dependency could not be resolved
 */
final class ResolutionException extends Exception
{
    /** @var string The name of the interface that could not be resolved */
    private string $interface;
    /** @var Context The context that the exception was thrown in */
    private Context $context;

    /**
     * @inheritdoc
     * @param string $interface The name of the interface that could not be resolved
     * @param Context $context The context that the exception was thrown in
     */
    public function __construct(string $interface, Context $context, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->interface = $interface;
        $this->context = $context;
    }

    /**
     * Gets the context that the exception was thrown in
     *
     * @return Context The context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * Gets the interface that could not be resolved
     *
     * @return string The interface that could not be resolved
     */
    public function getInterface(): string
    {
        return $this->interface;
    }
}
