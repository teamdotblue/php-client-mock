<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock\Comparator;

use Override;
use Ramsey\Uuid\Uuid;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use Stringable;

use function is_string;

/** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
class CouldBeAnyUuidComparator extends Comparator
{
    public const COULD_BE_ANY_UUID = '__**could be any uuid**__';

    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return $expected === self::COULD_BE_ANY_UUID;
    }

    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
    ): void {
        if (is_string($actual) && Uuid::isValid($actual)) {
            return;
        }

        throw new ComparisonFailure(
            $expected,
            $actual,
            is_string($expected) || $expected instanceof Stringable ? (string) $expected : '',
            is_string($actual) || $actual instanceof Stringable ? (string) $actual : '',
            'Failed asserting that the actual string is a uuid.',
        );
    }
}
