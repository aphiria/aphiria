<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Exception;

/**
 * Defines an exception that is thrown by an HTTP component
 */
class HttpException extends Exception
{
    /** @var IResponse The response */
    private IResponse $response;

    /**
     * @inheritdoc
     * @param int|IResponse $statusCodeOrResponse The status code or fully-formed response
     */
    public function __construct(
        int|IResponse $statusCodeOrResponse,
        string $message = '',
        int $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        if (\is_int($statusCodeOrResponse)) {
            $this->response = new Response($statusCodeOrResponse);
        } else {
            $this->response = $statusCodeOrResponse;
        }
    }

    /**
     * Gets the response
     *
     * @return IResponse The response
     */
    public function getResponse(): IResponse
    {
        return $this->response;
    }
}
