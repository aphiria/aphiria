<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Exception;

/**
 * Defines an exception that is thrown by an HTTP component
 */
class HttpException extends Exception
{
    /** @var int The HTTP status code */
    private $statusCode;
    /** @var HttpHeaders The headers to include */
    private $headers;

    /**
     * @inheritdoc
     * @param int $statusCode The HTTP status code
     * @param HttpHeaders|null $headers The HTTP headers
     */
    public function __construct(
        int $statusCode,
        string $message = '',
        HttpHeaders $headers = null,
        int $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->statusCode = $statusCode;
        $this->headers = $headers ?? new HttpHeaders();
    }

    /**
     * Gets the headers
     *
     * @return HttpHeaders The headers
     */
    public function getHeaders(): HttpHeaders
    {
        return $this->headers;
    }

    /**
     * Gets the HTTP status code
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
