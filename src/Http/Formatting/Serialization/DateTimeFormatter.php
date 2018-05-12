<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

use DateTime;

/**
 * Defines the DateTime formatter interceptor
 */
class DateTimeFormatter implements IEncodingInterceptor
{
    /** @var string The DateTime format to use */
    private $format;

    /**
     * @param string $format The DateTime format to use
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }

    /**
     * @inheritdoc
     */
    public function onDecoding($decodedValue, string $type)
    {
        if ($type !== DateTime::class) {
            return $decodedValue;
        }

        // Todo
    }

    /**
     * @inheritdoc
     */
    public function onEncoding($encodedValue, string $type)
    {
        if ($type !== DateTime::class) {
            return $encodedValue;
        }

        // Todo
    }
}
