<?php
/**
 * Hand-written quicksort — no usort(), no sort(), no SQL ORDER BY.
 *
 * This is the three-way variant: one pass splits the current range into
 * "less than the pivot", "equal to the pivot" and "greater than the pivot".
 * The equal group is finished immediately, which keeps duplicate prices or
 * equal ratings from causing repeated passes.
 *
 * Pivot choice: median of three (first, middle, last) to avoid the O(n^2)
 * behaviour that a fixed first-element pivot shows on already-sorted data.
 *
 * Time:  average O(n log n), worst case O(n^2)
 * Space: O(n) for the returned array (the partition works in place on that
 *        copy), plus O(log n) recursion stack on average.
 */
final class QuickSort
{
    /**
     * @param array<int, mixed> $items
     * @param callable(mixed, mixed): int $compare Negative, zero or positive.
     * @return array<int, mixed>
     */
    public static function sort(array $items, callable $compare): array
    {
        $count = count($items);
        if ($count < 2) {
            return $items;
        }

        self::quickSort($items, 0, $count - 1, $compare);

        return $items;
    }

    /**
     * @param array<int, mixed> $items
     * @param callable(mixed, mixed): int $compare
     */
    private static function quickSort(array &$items, int $low, int $high, callable $compare): void
    {
        // Tail-recursion elimination: loop over the larger side and recurse
        // into the smaller one, so the stack stays O(log n) on average.
        while ($low < $high) {
            [$lessEnd, $greaterStart] = self::partition($items, $low, $high, $compare);

            if ($lessEnd - $low < $high - $greaterStart) {
                self::quickSort($items, $low, $lessEnd - 1, $compare);
                $low = $greaterStart + 1;
            } else {
                self::quickSort($items, $greaterStart + 1, $high, $compare);
                $high = $lessEnd - 1;
            }
        }
    }

    /**
     * Three-way partition around a median-of-three pivot.
     *
     * After this call:
     *   [low .. lessEnd-1]      < pivot
     *   [lessEnd .. greaterStart] == pivot
     *   [greaterStart+1 .. high] > pivot
     *
     * @param array<int, mixed> $items
     * @param callable(mixed, mixed): int $compare
     * @return array{0: int, 1: int} The bounds of the equal-to-pivot block.
     */
    private static function partition(array &$items, int $low, int $high, callable $compare): array
    {
        $mid = intdiv($low + $high, 2);

        // Order low <= mid <= high, then move the median into the low slot.
        if ($compare($items[$mid], $items[$low]) < 0) {
            self::swap($items, $mid, $low);
        }
        if ($compare($items[$high], $items[$low]) < 0) {
            self::swap($items, $high, $low);
        }
        if ($compare($items[$high], $items[$mid]) < 0) {
            self::swap($items, $high, $mid);
        }
        self::swap($items, $mid, $low);

        $pivot = $items[$low];
        $lessEnd      = $low;
        $greaterStart = $high;
        $i            = $low + 1;

        while ($i <= $greaterStart) {
            $result = $compare($items[$i], $pivot);

            if ($result < 0) {
                self::swap($items, $lessEnd, $i);
                $lessEnd++;
                $i++;
            } elseif ($result > 0) {
                self::swap($items, $i, $greaterStart);
                $greaterStart--;
            } else {
                $i++;
            }
        }

        return [$lessEnd, $greaterStart];
    }

    /**
     * @param array<int, mixed> $items
     */
    private static function swap(array &$items, int $a, int $b): void
    {
        [$items[$a], $items[$b]] = [$items[$b], $items[$a]];
    }
}
