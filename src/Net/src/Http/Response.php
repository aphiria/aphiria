<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\ExtensionMethods\ExtensionMethods;
use InvalidArgumentException;

/**
 * Defines an HTTP response message
 */
class Response implements IResponse
{
    use ExtensionMethods;

    /** @var HttpStatusCode The response status code */
    protected HttpStatusCode $statusCode;
    /** @var string|null The response reason phrase if there is one, otherwise null */
    protected ?string $reasonPhrase;

    /**
     * @param HttpStatusCode|int $statusCode The response status code
     * @param Headers $headers The list of response headers
     * @param IBody|null $body The response body
     * @param string $protocolVersion The HTTP protocol version
     * @throws InvalidArgumentException Thrown if the status code was invalid
     */
    public function __construct(
        HttpStatusCode|int $statusCode = HttpStatusCode::Ok,
        protected readonly Headers $headers = new Headers(),
        protected ?IBody $body = null,
        protected readonly string $protocolVersion = '1.1'
    ) {
        $this->setStatusCode($statusCode);
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

    /**
     * @inheritdoc
     */
    public function getBody(): ?IBody
    {
        return $this->body;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * @inheritdoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritdoc
     */
    public function getReasonPhrase(): ?string
    {
        return $this->reasonPhrase;
    }

    /**
     * @inheritdoc
     */
    public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    /**
     * @inheritdoc
     */
    public function setBody(IBody $body): void
    {
        $this->body = $body;
    }

    /**
     * @inheritdoc
     */
    public function setStatusCode(HttpStatusCode|int $statusCode, ?string $reasonPhrase = null): void
    {
        if (\is_int($statusCode)) {
            $originalStatusCode = $statusCode;

            if (($statusCode = HttpStatusCode::tryFrom($originalStatusCode)) === null) {
                throw new InvalidArgumentException("Invalid HTTP status code $originalStatusCode");
            }
        }

        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase ?? HttpStatusCode::getDefaultReasonPhrase($this->statusCode);
    }
}
