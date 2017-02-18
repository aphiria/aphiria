<?php
namespace Opulence\Router\UriTemplates\Rules;

use InvalidArgumentException;

/**
 * Defines the between rule
 */
class BetweenRule implements IRule
{
    /** @var numeric The min value */
    private $min = 0;
    /** @var numeric The max value */
    private $max = 0;
    /** @var bool Whether or not the extremes are inclusive */
    private $isInclusive = true;

    /**
     * @param numeric $min The min value
     * @param numeric $max The max value
     * @param bool $isInclusive Whether or not the extremes are inclusive
     * @throws InvalidArgumentException Thrown if the min or max values are invalid
     */
    public function __construct($min, $max, bool $isInclusive = true)
    {
        if (!is_numeric($min)) {
            throw new InvalidArgumentException('Min value must be numeric');
        }

        if (!is_numeric($max)) {
            throw new InvalidArgumentException('Max value must be numeric');
        }

        $this->min = $min;
        $this->max = $max;
        $this->isInclusive = $isInclusive;
    }

    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'between';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        if ($this->isInclusive) {
            return $value >= $this->min && $value <= $this->max;
        } else {
            return $value > $this->min && $value < $this->max;
        }
    }
}
