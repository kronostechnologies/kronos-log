<?php

namespace Kronos\Tests\Log\Formatter\Exception;

use Kronos\Log\Formatter\Exception\NamespaceShrinker;
use PHPUnit\Framework\TestCase;

class NamespaceShrinkerTest extends TestCase
{
    public function test_namespacedClassName_shrink_shouldReturnShrunkName(): void
    {
        $namespacedClassName = __CLASS__;
        $parts = explode('\\', $namespacedClassName);
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $parts[$i] = substr($parts[$i], 0, 1);
        }
        $expectedShrunkName = implode('\\', $parts);
        $shrinker = new NamespaceShrinker();

        $actualShrunkName = $shrinker->shrink($namespacedClassName);

        self::assertEquals($expectedShrunkName, $actualShrunkName);
    }

    public function test_PreNamespaceClassNameWithUnderscores_shrink_shouldReturnShrunkName(): void
    {
        $namespacedClassName = 'Pre_Namespace_Era_ClassName';
        $parts = explode('_', $namespacedClassName);
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $parts[$i] = substr($parts[$i], 0, 1);
        }
        $expectedShrunkName = implode('_', $parts);
        $shrinker = new NamespaceShrinker();

        $actualShrunkName = $shrinker->shrink($namespacedClassName);

        self::assertEquals($expectedShrunkName, $actualShrunkName);
    }

    public function test_SimpleClassName_shrink_shouldReturnOriginalName(): void
    {
        $simpleClassName = 'ClassName';
        $shrinker = new NamespaceShrinker();

        $actualShrunkName = $shrinker->shrink($simpleClassName);

        self::assertEquals($simpleClassName, $actualShrunkName);
    }
}
