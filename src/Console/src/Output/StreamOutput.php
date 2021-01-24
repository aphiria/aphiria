<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output;

use Aphiria\Console\Output\Compilers\IOutputCompiler;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the stream output
 */
class StreamOutput extends Output
{
    /** @var resource The output stream */
    protected $outputStream;
    /** @var resource The input stream */
    protected $inputStream;

    /**
     * @param resource $outputStream The stream to write to
     * @param resource $inputStream The stream to read from
     * @param IOutputCompiler|null $compiler The output compiler to use
     * @throws InvalidArgumentException Thrown if the stream is not a resource
     * @psalm-suppress DocblockTypeContradiction Ditto We want check the types at runtime
     */
    public function __construct($outputStream, $inputStream, IOutputCompiler $compiler = null)
    {
        if (!\is_resource($outputStream) || !\is_resource($inputStream)) {
            throw new InvalidArgumentException('The stream must be a resource');
        }

        parent::__construct($compiler);

        $this->outputStream = $outputStream;
        $this->inputStream = $inputStream;
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        // Don't do anything
    }

    /**
     * @return resource
     */
    public function getOutputStream()
    {
        return $this->outputStream;
    }

    /**
     * @inheritdoc
     */
    public function readLine(): string
    {
        $input = \fgets($this->inputStream, 4096);

        if (!\feof($this->inputStream)) {
            throw new RuntimeException('Failed to read line');
        }

        return $input;
    }

    /**
     * @inheritdoc
     */
    protected function doWrite(string $message, bool $includeNewLine): void
    {
        \fwrite($this->outputStream, $message . ($includeNewLine ? PHP_EOL : ''));
        \fflush($this->outputStream);
    }
}
