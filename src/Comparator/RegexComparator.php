<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock\Comparator;

use Override;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use Stringable;

use function assert;
use function is_string;
use function preg_match;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function substr;

/** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
class RegexComparator extends Comparator
{
    public const REGEX = '__**REGEX**__';

    public static function wrap(string $expected): string
    {
        return self::REGEX . $expected . self::REGEX;
    }

    private static function unwrap(string $expected): string
    {
        return substr($expected, strlen(self::REGEX), -strlen(self::REGEX));
    }

    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return is_string($expected) && str_starts_with($expected, self::REGEX)
            && str_ends_with($expected, self::REGEX);
    }

    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
    ): void {
        assert(is_string($expected));
        $expected = self::unwrap($expected);

        if (is_string($actual) && preg_match('#^' . $expected . '$#', $actual)) {
            return;
        }

        throw new ComparisonFailure(
            $expected,
            $actual,
            $expected,
            is_string($actual) || $actual instanceof Stringable ? (string) $actual : '',
            'Failed asserting that the expected string is a regex of the actual string.',
        );
    }
}
