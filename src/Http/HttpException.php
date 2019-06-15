<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Exception;
use InvalidArgumentException;

/**
 * Defines an exception that is thrown by an HTTP component
 */
class HttpException extends Exception
{
    /** @var IHttpResponseMessage The response */
    private IHttpResponseMessage $response;

    /**
     * @inheritdoc
     * @param int|IHttpResponseMessage $statusCodeOrResponse The status code or fully-formed response
     * @throws InvalidArgumentException Thrown if the first parameter is neither a status code nor an HTTP response
     */
    public function __construct(
        $statusCodeOrResponse,
        string $message = '',
        int $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        if (is_int($statusCodeOrResponse)) {
            $this->response = new Response($statusCodeOrResponse);
        } elseif ($statusCodeOrResponse instanceof IHttpResponseMessage) {
            $this->response = $statusCodeOrResponse;
        } else {
            throw new InvalidArgumentException('First parameter must be either a status code or an HTTP response');
        }
    }

    /**
     * Gets the response
     *
     * @return IHttpResponseMessage The response
     */
    public function getResponse(): IHttpResponseMessage
    {
        return $this->response;
    }
}
