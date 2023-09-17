<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    private readonly IStream $outputStream;

    /**
     * @param IStream|null $outputStream The output stream to write the body to (null defaults to PHP's output stream)
     */
    public function __construct(IStream $outputStream = null)
    {
        $this->outputStream = $outputStream ?? new Stream(\fopen('php://output', 'wb'));
    }

    /**
     * Sets a response header
     *
     * @param string $value The value of the header
     * @param bool $replace Whether or not to replace existing header values
     * @note This method is useful for mocking header()
     */
    public function header(string $value, bool $replace = true): void
    {
        \header($value, $replace);
    }

    /**
     * @inheritdoc
     */
    public function headersAreSent(): bool
    {
        return \headers_sent();
    }

    /**
     * @inheritdoc
     */
    public function writeResponse(IResponse $response): void
    {
        if ($this->headersAreSent()) {
            return;
        }

        $startLine = "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()->value}";

        if (($reasonPhrase = $response->getReasonPhrase()) !== null) {
            $startLine .= " $reasonPhrase";
        }

        $this->header($startLine);

        foreach ($response->getHeaders() as $key => $value) {
            $headerName = (string)$key;

            if (isset(self::$headersToNotConcatenate[$headerName])) {
                /** @var string $headerValue */
                foreach ((array)$value as $headerValue) {
                    $this->header("$headerName: $headerValue", false);
                }
            } else {
                $this->header("$headerName: " . \implode(', ', \array_map(static fn (mixed $value): string => (string)$value, (array)$value)));
            }
        }

        if (($body = $response->getBody()) !== null) {
            $body->writeToStream($this->outputStream);
        }
    }
}
