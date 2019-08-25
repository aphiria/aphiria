<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations;

use Aphiria\Api\Controllers\Controller;
use Doctrine\Annotations\Reader;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

/**
 * Defines the class that finds controllers
 */
final class FileControllerFinder implements IControllerFinder
{
    /** @var Reader The annotation reader */
    private Reader $annotationReader;

    /**
     * @param Reader $annotationReader The annotation reader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @inheritdoc
     */
    public function findAll($paths): array
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        if (!is_array($paths)) {
            throw new InvalidArgumentException('Paths must be a string or array');
        }

        $allClassNames = [];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                throw new InvalidArgumentException("Path $path is not a directory");
            }

            $fileIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($fileIter as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $tokens = token_get_all(file_get_contents($file->getRealPath()));
                $allClassNames = [...$allClassNames, ...$this->getClassNamesFromTokens($tokens)];
            }
        }

        // Filter out any non-concrete controller classes
        $controllerClasses = array_filter($allClassNames, function ($className) {
            $class = new ReflectionClass($className);

            // Allow either Aphiria controllers or classes with the controller annotation
            return
                (
                    $class->isSubclassOf(Controller::class)
                    || $this->annotationReader->getClassAnnotation($class, 'Controller') !== null
                )
                && !$class->isInterface()
                && !$class->isAbstract();
        });

        return $controllerClasses;
    }

    /**
     * Gets the class names from a list of tokens
     * This will work even if multiple classes are defined in each file
     *
     * @param string[] $tokens The array of tokens
     * @return string[] The names of the classes
     */
    private function getClassNamesFromTokens(array $tokens): array
    {
        $classNames = [];
        $numTokens = count($tokens);
        $namespace = '';

        for ($i = 0;$i < $numTokens;$i++) {
            // Skip literals
            if (is_string($tokens[$i])) {
                continue;
            }

            $className = '';

            switch ($tokens[$i][0]) {
                case T_NAMESPACE:
                    $namespace = '';

                    // Collect all the namespace parts and separators
                    while (isset($tokens[++$i][1])) {
                        if (in_array($tokens[$i][0], [T_NS_SEPARATOR, T_STRING])) {
                            $namespace .= $tokens[$i][1];
                        }
                    }

                    break;
                case T_CLASS:
                    // Scan previous tokens to see if they're double colons, which would mean this is a class constant
                    for ($j = $i - 1;$j >= 0;$j--) {
                        if (!isset($tokens[$j][1])) {
                            break;
                        }

                        if ($tokens[$j][0] === T_DOUBLE_COLON) {
                            break 2;
                        }

                        if ($tokens[$j][0] === T_WHITESPACE) {
                            // Since we found whitespace, then we know this isn't a class constant
                            break;
                        }
                    }

                    // Get the class name
                    while (isset($tokens[++$i][1])) {
                        if ($tokens[$i][0] === T_STRING) {
                            $className .= $tokens[$i][1];
                            break;
                        }
                    }

                    $classNames[] = ltrim($namespace . '\\' . $className, '\\');
                    break 2;
            }
        }

        return $classNames;
    }
}
