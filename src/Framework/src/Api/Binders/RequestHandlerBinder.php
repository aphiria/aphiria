<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Binders;

use Aphiria\Api\ApiGateway;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\IRequestHandler;

/**
 * Defines the request handler binder
 */
final class RequestHandlerBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        // This will make it easier to grab the API gateway for integration tests
        $container->bindClass(IRequestHandler::class, ApiGateway::class);
    }
}
