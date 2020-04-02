<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

use Doctrine\Common\Annotations\AnnotationRegistry;
use Opis\Closure\ClosureStream;

$autoloader = require __DIR__.'/vendor/autoload.php';

/** @link https://github.com/opis/closure/issues/33 */
ClosureStream::register();

/** @link https://github.com/schmittjoh/serializer/issues/855 */
AnnotationRegistry::registerLoader([$autoloader, 'loadClass']);
