<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Net\Binders;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\RequestFactory;

/**
 * Defines the request binder
 */
class RequestBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        $request = (new RequestFactory)->createRequestFromSuperglobals($_SERVER);
        $container->bindInstance(IHttpRequestMessage::class, $request);
    }
}
