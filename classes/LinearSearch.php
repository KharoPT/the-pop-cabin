<?php
/**
 * Linear search over the catalog's dynamic array.
 *
 * filter() walks the array exactly once and keeps every product that satisfies
 * the caller's predicate. That is the O(n) "check each product against the
 * customer input" pass: name search, franchise filter, collection filter, price
 * range and stock status are all decided inside that one loop.
 */
final class LinearSearch
{
    /**
     * @param callable(mixed): bool $match
     * @param int|null $checks Receives how many products were compared (useful for demos/tests).
     * @return array<int, mixed>
     */
    public static function filter(DynamicArray $items, callable $match, ?int &$checks = null): array
    {
        $matches = [];
        $checks  = 0;

        foreach ($items as $item) {
            $checks++;
            if ($match($item)) {
                $matches[] = $item;
            }
        }

        return $matches;
    }

    /**
     * Stop at the first match. Still O(n) in the worst case, but it returns as
     * soon as the item is found.
     *
     * @param callable(mixed): bool $match
     */
    public static function first(DynamicArray $items, callable $match, ?int &$checks = null): mixed
    {
        $checks = 0;

        foreach ($items as $item) {
            $checks++;
            if ($match($item)) {
                return $item;
            }
        }

        return null;
    }
}
