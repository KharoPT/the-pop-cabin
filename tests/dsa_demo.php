<?php
/**
 * Hardcoded DSA demo — no database, no built-in sort.
 *
 * Run from the project root:
 *   C:\xampp\php\php.exe tests\dsa_demo.php
 *
 * This is the presentation-friendly version: the product list below is typed
 * into the file, then the custom DynamicArray, LinearSearch and QuickSort are
 * exercised on it so the output shows the data structure and both algorithms
 * without needing XAMPP/MySQL to be running.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This demo is CLI only.\n");
}

require_once __DIR__ . '/../classes/DynamicArray.php';
require_once __DIR__ . '/../classes/LinearSearch.php';
require_once __DIR__ . '/../classes/QuickSort.php';

$seed = [
    ['name' => 'Frieren',    'genre' => 'Anime',  'price' => 699.00,  'rating' => 4.8],
    ['name' => 'Fern',       'genre' => 'Anime',  'price' => 699.00,  'rating' => 4.6],
    ['name' => 'Himmel',     'genre' => 'Anime',  'price' => 499.00,  'rating' => 4.4],
    ['name' => 'Thanos',     'genre' => 'Movies', 'price' => 300.00,  'rating' => 3.9],
    ['name' => 'Spider-Man', 'genre' => 'Movies', 'price' => 300.00,  'rating' => 4.1],
    ['name' => 'Deku',       'genre' => 'Anime',  'price' => 349.00,  'rating' => 4.7],
    ['name' => 'Dobby',      'genre' => 'Movies', 'price' => 799.00,  'rating' => 4.2],
    ['name' => 'Braum',      'genre' => 'Games',  'price' => 1299.00, 'rating' => 4.0],
];

function printTable(string $title, array $rows): void
{
    echo "\n{$title}\n";
    printf("  %-20s %-8s %10s %8s\n", 'Name', 'Genre', 'Price', 'Rating');
    echo '  ' . str_repeat('-', 50) . "\n";
    foreach ($rows as $row) {
        printf(
            "  %-20s %-8s %10s %8.1f\n",
            $row['name'],
            $row['genre'],
            'PHP ' . number_format((float) $row['price'], 2),
            (float) $row['rating']
        );
    }
}

echo "==================================================\n";
echo " THE POP CABIN — HARDCODED DSA DEMO\n";
echo "==================================================\n";

echo "\n== 1. Dynamic array ==\n";

$catalog = new DynamicArray(2);
echo 'Start: size ' . $catalog->count() . ', capacity ' . $catalog->capacity() . "\n";

foreach ($seed as $product) {
    $catalog->add($product);
}

echo 'After ' . $catalog->count() . ' adds: capacity ' . $catalog->capacity() . "\n";
echo 'Slots are doubled when full, so the catalog grows without re-creating the array.' . "\n";
printTable('Catalog as loaded', $catalog->toArray());

echo "\n== 2. Linear search ==\n";

$checks = 0;
$hits   = LinearSearch::filter(
    $catalog,
    static fn(array $product): bool => stripos($product['name'], 'an') !== false,
    $checks
);

echo "Search term: \"an\" (matches the product name)\n";
echo 'Compared ' . $checks . ' products, found ' . count($hits) . ".\n";
printTable('Matches', $hits);
echo "Complexity: O(n) — one pass over the whole array.\n";

$firstCheck = 0;
$first      = LinearSearch::first(
    $catalog,
    static fn(array $product): bool => $product['genre'] === 'Games',
    $firstCheck
);

echo "\nFirst product in Games: " . ($first['name'] ?? 'none') . " (stopped after {$firstCheck} checks).\n";

echo "\n== 3. QuickSort ==\n";

$byPrice = static function (array $a, array $b): int {
    $price = $a['price'] <=> $b['price'];
    return $price !== 0 ? $price : strcasecmp($a['name'], $b['name']);
};

$byRating = static function (array $a, array $b): int {
    $rating = $b['rating'] <=> $a['rating'];
    return $rating !== 0 ? $rating : strcasecmp($a['name'], $b['name']);
};

$byName = static fn(array $a, array $b): int => strcasecmp($a['name'], $b['name']);

$comparisons = 0;
$priceAsc = QuickSort::sort(
    $catalog->toArray(),
    static function (array $a, array $b) use ($byPrice, &$comparisons): int {
        $comparisons++;
        return $byPrice($a, $b);
    }
);
printTable('Price: low to high', $priceAsc);
echo "QuickSort compared pairs {$comparisons} times for 8 products (average O(n log n)).\n";

$priceDesc = QuickSort::sort(
    $catalog->toArray(),
    static fn(array $a, array $b): int => $byPrice($b, $a)
);
printTable('Price: high to low', $priceDesc);

printTable('Rating: high to low', QuickSort::sort($catalog->toArray(), $byRating));
printTable('Name: A to Z', QuickSort::sort($catalog->toArray(), $byName));
printTable('Name: Z to A', QuickSort::sort($catalog->toArray(), static fn(array $a, array $b): int => $byName($b, $a)));

echo "\n==================================================\n";
echo " Complexity summary\n";
echo "==================================================\n";
echo " DynamicArray : add O(1) amortised, get/set O(1), remove O(n)\n";
echo " Linear search: O(n) time, O(k) result space\n";
echo " QuickSort    : O(n log n) average, O(n^2) worst; O(n) output + O(log n) stack\n";
echo " No usort(), sort(), asort() or SQL ORDER BY was used above.\n";
