<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Tests;

use Aphiria\RouteAnnotations\Annotations\Get;
use Aphiria\RouteAnnotations\Annotations\Middleware;
use Doctrine\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 *
 */
class DummyTest extends TestCase
{
    public function testAnnotationsAreWorking(): void
    {
        $reader = new AnnotationReader();
        $classAnnotations = $reader->getClassAnnotations(new ReflectionClass(TestController::class));
        $this->assertCount(2, $classAnnotations);

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof Middleware) {
                $this->assertContains($annotation->className, ['Foo', 'someMiddleware']);
                $this->assertEquals(['foo' => 'bar'], $annotation->attributes);
            }
        }

        $methodAnnotations = $reader->getMethodAnnotations(new ReflectionMethod(TestController::class, 'route1'));

        $this->assertCount(1, $methodAnnotations);

        $this->assertInstanceOf(Get::class, $methodAnnotations[0]);
        $this->assertEquals('foo', $methodAnnotations[0]->path);
    }
}
