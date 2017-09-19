<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Tests\Net\Http\Requests\Mocks;

use Opulence\Net\Http\Requests\UploadedFile as BaseUploadedFile;

/**
 * Mocks the uploaded file
 */
class UploadedFile extends BaseUploadedFile
{
    /**
     * @inheritDoc
     */
    protected function doMove(string $target) : void
    {
        copy($this->getPathname(), $target);
    }
}
