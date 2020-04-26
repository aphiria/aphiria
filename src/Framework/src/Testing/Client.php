<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Testing;

use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines a request client for tests
 */
class Client
{
    /**
     * Sends a request and gets a response
     *
     * @param IRequest $request The request to send
     * @return IResponse The returned response
     */
    public function send(IRequest $request): IResponse
    {
        // TODO
    }
}
