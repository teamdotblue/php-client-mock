<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock\Comparator;

use Override;
use SebastianBergmann\Comparator\Comparator;

/** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
class CouldBeAnythingComparator extends Comparator
{
    public const COULD_BE_ANYTHING = '__**could be anything**__';

    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return $expected === self::COULD_BE_ANYTHING;
    }

    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
    ): void {
    }
}
