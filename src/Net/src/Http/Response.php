<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use InvalidArgumentException;

/**
 * Defines an HTTP response message
 */
class Response implements IResponse
{
    /** @inheritdoc */
    public ?IBody $body;
    /** @inheritdoc */
    public private(set) Headers $headers;
    /** @inheritdoc */
    public private(set) IDictionary $propertie;
    /** @inheritdoc */
    public private(set) string $protocolVersion;
    /** @inheritdoc */
    public ?string $reasonPhrase;
    /** @inheritdoc */
    public HttpStatusCode $statusCode {
        set (HttpStatusCode|int $value) {
            if (\is_int($value)) {
                $originalStatusCode = $value;

                if (($value = HttpStatusCode::tryFrom($originalStatusCode)) === null) {
                    throw new InvalidArgumentException("Invalid HTTP status code $originalStatusCode");
                }
            }

            $this->statusCode = $value;
            $this->reasonPhrase = HttpStatusCode::getDefaultReasonPhrase($this->statusCode);
        }
    }

    /**
     * @param HttpStatusCode|int $statusCode The response status code
     * @param Headers $headers The list of response headers
     * @param IBody|null $body The response body
     * @param string $protocolVersion The HTTP protocol version
     * @throws InvalidArgumentException Thrown if the status code was invalid
     */
    public function __construct(
        HttpStatusCode|int $statusCode = HttpStatusCode::Ok,
        Headers $headers = new Headers(),
        ?IBody $body = null,
        string $protocolVersion = '1.1'
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
        $this->protocolVersion = $protocolVersion;
        $this->reasonPhrase = HttpStatusCode::getDefaultReasonPhrase($this->statusCode);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        $startLine = "HTTP/{$this->protocolVersion} {$this->statusCode->value}";

        if ($this->reasonPhrase !== null) {
            $startLine .= " {$this->reasonPhrase}";
        }

        $headers = '';

        if (\count($this->headers) > 0) {
            $headers .= "\r\n{$this->headers}";
        }

        $response = $startLine . $headers . "\r\n\r\n";

        if ($this->body !== null) {
            $response .= $this->body;
        }

        return $response;
    }
}
