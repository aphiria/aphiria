<?php
namespace Opulence\Router\UriTemplates\Rules;

use DateTime;

/**
 * Defines the date rule
 */
class DateRule
{
    /** @var The list of acceptable date formats */
    private $formats = [];

    /**
     * @param array|string $formats The format or list of acceptable formats
     */
    public function __construct($formats)
    {
        $this->formats = (array)$formats;
    }

    /**
     * @inheritdoc
     */
    public function getSlug() : string
    {
        return 'date';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        foreach ($this->formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $value);

            if ($dateTime !== false && $value == $dateTime->format($format)) {
                return true;
            }
        }
        return false;
    }
}
