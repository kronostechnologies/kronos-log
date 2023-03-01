<?php

namespace Kronos\Tests\Log;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;

abstract class ExtendedTestCase extends TestCase
{
    public static function withConsecutive(array $firstCallArguments, array ...$consecutiveCallsArguments)
    {
        $allConsecutiveCallsArguments = [$firstCallArguments, ...$consecutiveCallsArguments];

        $maxNumberOfArguments = 0;
        foreach ($allConsecutiveCallsArguments as $consecutiveCallArguments) {
            $numberOfArguments = count($consecutiveCallArguments);
            $maxNumberOfArguments = max($maxNumberOfArguments, $numberOfArguments);
        }

        $argumentList = [];
        for ($argumentPosition = 0; $argumentPosition < $maxNumberOfArguments; $argumentPosition++) {
            $argumentList[$argumentPosition] = array_column($allConsecutiveCallsArguments, $argumentPosition);
        }

        $mockedMethodCall = 0;
        $callbackCall = 0;
        foreach ($argumentList as $index => $argument) {
            yield new Callback(
                static function (mixed $actualArgument) use (
                    $argumentList,
                    &$mockedMethodCall,
                    &$callbackCall,
                    $index,
                    $maxNumberOfArguments
                ): bool {
                    $expected = $argumentList[$index][$mockedMethodCall] ?? null;

                    $callbackCall++;
                    $mockedMethodCall = (int) ($callbackCall / $maxNumberOfArguments);

                    if ($expected instanceof Constraint) {
                        self::assertThat($actualArgument, $expected);
                    } else {
                        self::assertEquals($expected, $actualArgument);
                    }

                    return true;
                },
            );
        }
    }
}
