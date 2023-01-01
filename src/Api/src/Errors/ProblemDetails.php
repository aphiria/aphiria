<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Errors;

use Aphiria\Net\Http\HttpStatusCode;

/**
 * Defines standard problem details that conform to RFC 7807
 * @link https://tools.ietf.org/html/rfc7807
 */
class ProblemDetails
{
    /** @var int The HTTP status code([RFC7231], Section 6) generated by the origin server for this occurrence of the problem. */
    public int $status;

    /**
     * @param string|null $type A URI reference [RFC3986] that identifies the problem type. This specification encourages that, when dereferenced, it provide human-readable documentation for the problem type (e.g., using HTML [W3C.REC-html5-20141028]). When this member is not present, its value is assumed to be "about:blank".
     * @param string|null $title A short, human-readable summary of the problem type.It SHOULD NOT change from occurrence to occurrence of the problem, except for purposes of localization(e.g., using proactive content negotiation; see[RFC7231], Section 3.4).
     * @param string|null $detail A human-readable explanation specific to this occurrence of the problem.
     * @param HttpStatusCode|int $status The HTTP status code([RFC7231], Section 6) generated by the origin server for this occurrence of the problem.
     * @param string|null $instance A URI reference that identifies the specific occurrence of the problem.It may or may not yield further information if dereferenced.
     * @param array|null $extensions The mapping of any custom extension names to values
     * @link https://tools.ietf.org/html/rfc7807#section-3.1
     * @link https://tools.ietf.org/html/rfc7807#section-3.2
     */
    public function __construct(
        public ?string $type = null,
        public ?string $title = null,
        public ?string $detail = null,
        HttpStatusCode|int $status = HttpStatusCode::InternalServerError,
        public ?string $instance = null,
        public ?array $extensions = null
    ) {
        if ($status instanceof HttpStatusCode) {
            $status = $status->value;
        }

        $this->status = $status;
    }
}
