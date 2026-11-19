<?php
/**
 * DSA test cases for the catalog.
 *
 * Run from the project root:
 *   C:\xampp\php\php.exe tests\catalog_algorithms_test.php
 *
 * The ProductCatalog tests use an in-memory SQLite database with the same table
 * and column names as the real schema, so the algorithm layer can be checked
 * without starting MySQL. No built-in sort is used by the implementation; the
 * expected arrays below are written by hand.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This test runner is CLI only.\n");
}

require_once __DIR__ . '/../classes/ProductCatalog.php';

$passed = 0;
$failed = 0;

function check(string $label, mixed $expected, mixed $actual): void
{
    global $passed, $failed;

    if ($expected === $actual) {
        $passed++;
        echo "PASS  {$label}\n";
        return;
    }

    $failed++;
    echo "FAIL  {$label}\n";
    echo "      expected: " . json_encode($expected) . "\n";
    echo "      actual:   " . json_encode($actual) . "\n";
}

function checkThrows(string $label, callable $operation): void
{
    global $passed, $failed;

    try {
        $operation();
    } catch (Throwable $e) {
        $passed++;
        echo "PASS  {$label} (" . get_class($e) . ")\n";
        return;
    }

    $failed++;
    echo "FAIL  {$label} (no exception thrown)\n";
}

function names(array $rows): array
{
    return array_map(static fn(array $row): string => (string) $row['name'], $rows);
}

echo "== DynamicArray ==\n";

$array = new DynamicArray(2);
check('starts empty', 0, $array->count());
$array->add(['id' => 1]);
$array->add(['id' => 2]);
check('two adds fill the initial capacity', 2, $array->capacity());
$array->add(['id' => 3]);
check('third add doubles the capacity', 4, $array->capacity());
check('count tracks used slots', 3, $array->count());
check('get returns the stored item', ['id' => 2], $array->get(1));
$array->set(1, ['id' => 20]);
check('set replaces an item', ['id' => 20], $array->get(1));
check('toArray returns only used slots', 3, count($array->toArray()));

$removed = $array->removeAt(1);
check('removeAt returns the removed item', ['id' => 20], $removed);
check('removeAt shifts the remaining items', [['id' => 1], ['id' => 3]], $array->toArray());
checkThrows('get rejects an out-of-range index', static fn() => $array->get(99));
check('fromArray copies every item', 4, DynamicArray::fromArray([1, 2, 3, 4])->count());

echo "\n== LinearSearch ==\n";

$numbers = DynamicArray::fromArray([
    ['n' => 1],
    ['n' => 2],
    ['n' => 3],
    ['n' => 4],
]);

$checks = 0;
$evens  = LinearSearch::filter($numbers, static fn(array $row): bool => $row['n'] % 2 === 0, $checks);
check('filter keeps matching items', [['n' => 2], ['n' => 4]], $evens);
check('filter compares every item once', 4, $checks);

$checks = 0;
$found  = LinearSearch::first($numbers, static fn(array $row): bool => $row['n'] === 3, $checks);
check('first returns the first match', ['n' => 3], $found);
check('first stops at the match', 3, $checks);

$checks = 0;
check('filter on empty array returns empty', [], LinearSearch::filter(new DynamicArray(), static fn(): bool => true, $checks));
check('empty array compares nothing', 0, $checks);

echo "\n== QuickSort ==\n";

$numeric = static fn(mixed $a, mixed $b): int => $a <=> $b;
check('empty array', [], QuickSort::sort([], $numeric));
check('single item', [7], QuickSort::sort([7], $numeric));
check('two items', [1, 2], QuickSort::sort([2, 1], $numeric));
check('three items', [1, 2, 3], QuickSort::sort([3, 1, 2], $numeric));
check('reverse sorted', [1, 2, 3, 4, 5], QuickSort::sort([5, 4, 3, 2, 1], $numeric));
check('already sorted', [1, 2, 3, 4, 5], QuickSort::sort([1, 2, 3, 4, 5], $numeric));
check('duplicates', [1, 1, 2, 2, 3], QuickSort::sort([2, 2, 1, 3, 1], $numeric));
check('all equal', [7, 7, 7, 7], QuickSort::sort([7, 7, 7, 7], $numeric));
check('negatives', [-10, -1, 0, 3, 5], QuickSort::sort([0, -1, 5, -10, 3], $numeric));

$sortedRange = range(1, 500);
check('sorted range of 500', $sortedRange, QuickSort::sort($sortedRange, $numeric));
check('reversed range of 500', $sortedRange, QuickSort::sort(range(500, 1), $numeric));

$words = ['pear', 'apple', 'orange', 'banana'];
check(
    'string comparator',
    ['apple', 'banana', 'orange', 'pear'],
    QuickSort::sort($words, static fn(string $a, string $b): int => strcmp($a, $b))
);

echo "\n== ProductCatalog (SQLite fixture) ==\n";

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec('CREATE TABLE categories (category_id INTEGER PRIMARY KEY, category_name TEXT NOT NULL, slug TEXT NOT NULL)');
$db->exec('CREATE TABLE products (
    product_id INTEGER PRIMARY KEY,
    category_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    slug TEXT NOT NULL,
    description TEXT NOT NULL DEFAULT "",
    price NUMERIC NOT NULL,
    stock_quantity INTEGER NOT NULL,
    image_url TEXT NOT NULL DEFAULT "",
    is_featured INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT ""
)');
$db->exec('CREATE TABLE collections (collection_id INTEGER PRIMARY KEY, collection_name TEXT NOT NULL, slug TEXT NOT NULL)');
$db->exec('CREATE TABLE product_collections (product_id INTEGER NOT NULL, collection_id INTEGER NOT NULL)');
$db->exec('CREATE TABLE reviews (review_id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL, rating INTEGER NOT NULL)');

$db->exec("INSERT INTO categories (category_id, category_name, slug) VALUES
    (1, 'Frieren: Beyond Journey''s End', 'frieren'),
    (2, 'Marvel', 'marvel')");
$db->exec("INSERT INTO collections (collection_id, collection_name, slug) VALUES
    (1, 'Anime', 'anime'),
    (2, 'Movies', 'movies'),
    (3, 'Games', 'games')");

$insertProduct = $db->prepare(
    'INSERT INTO products (product_id, category_id, name, slug, price, stock_quantity, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);

$fixture = [
    [1, 1, 'Frieren', 'frieren', 699.00, 2, '2026-01-01 10:00:00'],
    [2, 1, 'Fern', 'fern', 699.00, 0, '2026-01-02 10:00:00'],
    [3, 1, 'Himmel', 'himmel', 499.00, 1, '2026-01-03 10:00:00'],
    [4, 2, 'Thanos', 'thanos', 300.00, 9, '2026-01-04 10:00:00'],
    [5, 2, 'Spider-Man', 'spider-man', 300.00, 0, '2026-01-05 10:00:00'],
    [6, 2, 'Cheap Pop', 'cheap-pop', 100.00, 5, '2026-01-06 10:00:00'],
];

foreach ($fixture as $row) {
    $insertProduct->execute($row);
}

$db->exec('INSERT INTO product_collections (product_id, collection_id) VALUES
    (1, 1), (2, 1), (3, 1), (4, 2), (5, 2), (6, 3)');
$db->exec('INSERT INTO reviews (review_id, product_id, rating) VALUES
    (1, 1, 5), (2, 1, 4), (3, 2, 5), (4, 4, 3)');

$catalog = new ProductCatalog($db);

check('loads every product', 6, $catalog->count());
check('reads the review average', 4.5, $catalog->all()[0]['average_rating']);
check('reads the review count', 2, $catalog->all()[0]['review_count']);

// Normal search: matches the product name and the franchise name.
check('search by franchise returns all three anime pops', ['Frieren', 'Fern', 'Himmel'], names($catalog->filter(['search' => 'frieren'])));
check('search is case-insensitive', 1, count($catalog->filter(['search' => 'THANOS'])));

// Empty, unmatched and invalid input.
check('empty search returns everything', 6, count($catalog->filter(['search' => ''])));
check('unmatched search returns nothing', 0, count($catalog->filter(['search' => 'no-such-pop'])));
check('invalid array search is ignored', 6, count($catalog->filter(['search' => ['thanos']])));
check('invalid array sort falls back to newest', ['Cheap Pop', 'Spider-Man', 'Thanos', 'Himmel', 'Fern', 'Frieren'], names($catalog->results(['sort' => ['price_asc']])));
check('invalid min price is ignored', 6, count($catalog->filter(['min_price' => 'abc'])));
check('invalid max price is ignored', 6, count($catalog->filter(['max_price' => 'abc'])));
check('percent sign is treated literally', 0, count($catalog->filter(['search' => '%'])));

// Filters.
check('franchise filter', 3, count($catalog->filter(['category' => 'marvel'])));
check('collection filter', 3, count($catalog->filter(['collection' => 'anime'])));
check('price range filter', ['Himmel', 'Thanos', 'Spider-Man'], names($catalog->filter(['min_price' => '300', 'max_price' => '500'])));
check('in-stock filter', 4, count($catalog->filter(['stock' => 'in'])));
check('out-of-stock filter', 2, count($catalog->filter(['stock' => 'out'])));

// Sorting: hand-checked expected orders.
$all = $catalog->all();

check(
    'price ascending',
    ['Cheap Pop', 'Spider-Man', 'Thanos', 'Himmel', 'Fern', 'Frieren'],
    names($catalog->sort($all, 'price_asc'))
);
check(
    'price descending',
    ['Fern', 'Frieren', 'Himmel', 'Spider-Man', 'Thanos', 'Cheap Pop'],
    names($catalog->sort($all, 'price_desc'))
);
check(
    'rating descending',
    ['Fern', 'Frieren', 'Thanos', 'Cheap Pop', 'Himmel', 'Spider-Man'],
    names($catalog->sort($all, 'rating_desc'))
);
check(
    'rating ascending',
    ['Cheap Pop', 'Himmel', 'Spider-Man', 'Thanos', 'Frieren', 'Fern'],
    names($catalog->sort($all, 'rating_asc'))
);
check(
    'name ascending',
    ['Cheap Pop', 'Fern', 'Frieren', 'Himmel', 'Spider-Man', 'Thanos'],
    names($catalog->sort($all, 'name'))
);
check(
    'name descending',
    ['Thanos', 'Spider-Man', 'Himmel', 'Frieren', 'Fern', 'Cheap Pop'],
    names($catalog->sort($all, 'name_desc'))
);
check(
    'newest is the default',
    ['Cheap Pop', 'Spider-Man', 'Thanos', 'Himmel', 'Fern', 'Frieren'],
    names($catalog->sort($all, 'not-a-sort'))
);

// Combined filter + sort, and pagination.
$cheapAnime = $catalog->results(['collection' => 'anime', 'max_price' => '600', 'sort' => 'name']);
check('filter then sort', ['Himmel'], names($cheapAnime));

$pageTwo = ProductCatalog::page($catalog->sort($all, 'name'), 2, 2);
check('page 2, two per page', ['Frieren', 'Himmel'], names($pageTwo));

// Empty catalog: same schema, no rows.
$db->exec('DELETE FROM reviews');
$db->exec('DELETE FROM product_collections');
$db->exec('DELETE FROM products');

$emptyCatalog = new ProductCatalog($db);
check('empty catalog loads no products', 0, $emptyCatalog->count());
check('empty catalog search returns nothing', [], $emptyCatalog->filter(['search' => 'anything']));
check('empty catalog page is empty', [], ProductCatalog::page([], 1, 16));

echo "\n----------------------------------------\n";
echo "Passed: {$passed}  Failed: {$failed}\n";
exit($failed === 0 ? 0 : 1);
