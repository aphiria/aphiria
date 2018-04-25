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
class DateTimeFormatter implements ISerializationInterceptor
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
    public function onDeserialization($contract, string $type)
    {
        if ($type !== DateTime::class) {
            return $contract;
        }

        // Todo
    }

    /**
     * @inheritdoc
     */
    public function onSerialization($contract, string $type)
    {
        if ($type !== DateTime::class) {
            return $contract;
        }

        // Todo
    }
}
