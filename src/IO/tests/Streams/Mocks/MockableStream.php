<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\IO\Tests\Streams\Mocks;

use Aphiria\IO\Streams\IStream;

/**
 * Defines a stream class that is mockable
 */
abstract class MockableStream implements IStream
{
    /** @inheritdoc */
    public bool $isEof = true;
    /** @inheritdoc */
    public bool $isReadable = true;
    /** @inheritdoc */
    public bool $isSeekable = true;
    /** @inheritdoc */
    public bool $isWritable = true;
    /** @inheritdoc */
    public ?int $length = null;
    /** @inheritdoc */
    public int $position = 0;
}
