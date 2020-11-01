<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;

/**
 * Defines the stream response writer
 */
class StreamResponseWriter implements IResponseWriter
{
    /** @var array<string, true> The hash table of headers that should not concatenate multiple values */
    protected static array $headersToNotConcatenate = ['Set-Cookie' => true, 'Www-Authenticate' => true, 'Proxy-Authenticate' => true];
    /** @var IStream The output stream to write the body to */
    private IStream $outputStream;

    /**
     * @param IStream|null $outputStream The output stream to write the body to (null defaults to PHP's output stream)
     */
    public function __construct(IStream $outputStream = null)
    {
        $this->outputStream = $outputStream ?? new Stream(fopen('php://output', 'wb'));
    }

    /**
     * Sets a response header
     * Note: This method is useful for mocking header()
     *
     * @param string $value The value of the header
     * @param bool $replace Whether or not to replace existing header values
     */
    public function header(string $value, bool $replace = true): void
    {
        header($value, $replace);
    }

    /**
     * @inheritdoc
     */
    public function headersAreSent(): bool
    {
        return headers_sent();
    }

    /**
     * @inheritdoc
     */
    public function writeResponse(IResponse $response): void
    {
        if ($this->headersAreSent()) {
            return;
        }

        $startLine = "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()}";

        if (($reasonPhrase = $response->getReasonPhrase()) !== null) {
            $startLine .= " $reasonPhrase";
        }

        $this->header($startLine);

        foreach ($response->getHeaders() as $kvp) {
            $headerName = $kvp->getKey();

            if (isset(self::$headersToNotConcatenate[$headerName])) {
                foreach ($kvp->getValue() as $headerValue) {
                    $this->header("$headerName: $headerValue", false);
                }
            } else {
                $this->header("$headerName: " . implode(', ', $kvp->getValue()));
            }
        }

        if (($body = $response->getBody()) !== null) {
            $body->writeToStream($this->outputStream);
        }
    }
}
