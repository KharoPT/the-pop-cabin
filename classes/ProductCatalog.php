<?php
require_once __DIR__ . '/DynamicArray.php';
require_once __DIR__ . '/LinearSearch.php';
require_once __DIR__ . '/QuickSort.php';
require_once __DIR__ . '/../config/database.php';

/**
 * The public product catalog, held in memory as a dynamic array.
 *
 * Product data is read from MySQL once, placed into DynamicArray, and then all
 * customer-facing work happens in PHP:
 *
 *   load()    one read per request, O(n)
 *   filter()  one linear pass over the array, O(n)
 *   sort()    hand-written quicksort, O(n log n) average
 *   page()    array_slice of the sorted result, O(k)
 *
 * The admin still writes to the products/categories/collections/reviews tables;
 * the next catalog load picks those changes up. Keeping the reads here means
 * search, filter and sort share one source of truth instead of a separate
 * WHERE/ORDER BY query for each customer action.
 */
final class ProductCatalog
{
    private PDO $db;
    private DynamicArray $products;

    /** @var array<int, string[]> product_id => collection slugs (the genre pills) */
    private array $collectionSlugs = [];

    /** Sort keys accepted by sort(); anything else falls back to "newest". */
    public const SORTS = [
        'newest',
        'price_asc',
        'price_desc',
        'rating_asc',
        'rating_desc',
        'name',
        'name_desc',
    ];

    public function __construct(?PDO $db = null)
    {
        $this->db       = $db ?? Database::getInstance()->getConnection();
        $this->products = new DynamicArray();
        $this->load();
    }

    /** The whole catalog, in the order it was read from the table. */
    public function all(): array
    {
        return $this->products->toArray();
    }

    public function count(): int
    {
        return $this->products->count();
    }

    /**
     * Filter + sort in one call. Used by shop.php.
     *
     * @param array{search?: string, category?: string, collection?: string,
     *              min_price?: string|float|int, max_price?: string|float|int,
     *              stock?: string, sort?: string} $filters
     * @return array<int, array>
     */
    public function results(array $filters = []): array
    {
        $items = $this->filter($filters);
        $sort  = is_string($filters['sort'] ?? null) ? $filters['sort'] : '';

        return $this->sort($items, $sort);
    }

    /**
     * One linear pass: every product is checked against every active filter.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array>
     */
    public function filter(array $filters = []): array
    {
        $search     = is_string($filters['search'] ?? null) ? trim($filters['search']) : '';
        $category   = is_string($filters['category'] ?? null) ? trim($filters['category']) : '';
        $collection = is_string($filters['collection'] ?? null) ? trim($filters['collection']) : '';
        $minPrice   = self::numberOrNull($filters['min_price'] ?? null);
        $maxPrice   = self::numberOrNull($filters['max_price'] ?? null);
        $stock      = in_array($filters['stock'] ?? '', ['in', 'out'], true) ? $filters['stock'] : '';

        return LinearSearch::filter(
            $this->products,
            static function (array $product) use ($search, $category, $collection, $minPrice, $maxPrice, $stock): bool {
                if ($search !== '') {
                    $name      = (string) ($product['name'] ?? '');
                    $franchise = (string) ($product['category_name'] ?? '');

                    if (!self::containsIgnoreCase($name, $search) && !self::containsIgnoreCase($franchise, $search)) {
                        return false;
                    }
                }

                if ($category !== '' && strcasecmp((string) ($product['category_slug'] ?? ''), $category) !== 0) {
                    return false;
                }

                if ($collection !== '' && !self::hasCollection($product['collection_slugs'] ?? [], $collection)) {
                    return false;
                }

                $price = (float) ($product['price'] ?? 0);
                if ($minPrice !== null && $price < $minPrice) {
                    return false;
                }
                if ($maxPrice !== null && $price > $maxPrice) {
                    return false;
                }

                $quantity = (int) ($product['stock_quantity'] ?? 0);
                if ($stock === 'in' && $quantity <= 0) {
                    return false;
                }
                if ($stock === 'out' && $quantity > 0) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * Sort with the hand-written QuickSort class.
     *
     * Price and rating sort both ways; name sorts A-Z and Z-A; "newest" keeps
     * the old default order. Ties fall back to name (A-Z) and product id so
     * the result is deterministic even though quicksort is not stable.
     *
     * @param array<int, array> $items
     * @return array<int, array>
     */
    public function sort(array $items, string $sort): array
    {
        if (!in_array($sort, self::SORTS, true)) {
            $sort = 'newest';
        }

        $direction = str_ends_with($sort, '_desc') ? -1 : 1;

        $byPrice = static function (array $a, array $b) use ($direction): int {
            $price = $direction * ((float) $a['price'] <=> (float) $b['price']);
            return $price !== 0 ? $price : self::compareNames($a, $b);
        };

        $byRating = static function (array $a, array $b) use ($direction): int {
            $rating = $direction * ((float) ($a['average_rating'] ?? 0) <=> (float) ($b['average_rating'] ?? 0));
            if ($rating !== 0) {
                return $rating;
            }

            $reviews = $direction * ((int) ($a['review_count'] ?? 0) <=> (int) ($b['review_count'] ?? 0));
            return $reviews !== 0 ? $reviews : self::compareNames($a, $b);
        };

        $byNameAsc = static function (array $a, array $b): int {
            return self::compareNames($a, $b);
        };

        $byNameDesc = static function (array $a, array $b): int {
            $name = strcasecmp((string) $b['name'], (string) $a['name']);
            return $name !== 0 ? $name : ((int) $a['product_id'] <=> (int) $b['product_id']);
        };

        $byNewest = static function (array $a, array $b): int {
            $date = strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
            return $date !== 0 ? $date : ((int) $b['product_id'] <=> (int) $a['product_id']);
        };

        return match ($sort) {
            'price_asc', 'price_desc'   => QuickSort::sort($items, $byPrice),
            'rating_asc', 'rating_desc' => QuickSort::sort($items, $byRating),
            'name'                      => QuickSort::sort($items, $byNameAsc),
            'name_desc'                 => QuickSort::sort($items, $byNameDesc),
            default                     => QuickSort::sort($items, $byNewest),
        };
    }

    /** Slice an already sorted result for the pager. */
    public static function page(array $items, int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $page    = max(1, $page);

        return array_slice($items, ($page - 1) * $perPage, $perPage);
    }

    /**
     * Read the catalog and the two lookup sources (collections, review averages)
     * into the dynamic array. O(n) in the number of products.
     */
    private function load(): void
    {
        $rows = $this->db->query(
            'SELECT p.*, c.category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.category_id
             ORDER BY p.product_id ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        $collectionRows = $this->db->query(
            'SELECT pc.product_id, col.slug
             FROM product_collections pc
             JOIN collections col ON col.collection_id = pc.collection_id'
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($collectionRows as $row) {
            $this->collectionSlugs[(int) $row['product_id']][] = (string) $row['slug'];
        }

        $ratingRows = $this->db->query(
            'SELECT product_id, AVG(rating) AS average_rating, COUNT(*) AS review_count
             FROM reviews
             GROUP BY product_id'
        )->fetchAll(PDO::FETCH_ASSOC);

        $ratings = [];
        foreach ($ratingRows as $row) {
            $ratings[(int) $row['product_id']] = [
                'average' => round((float) $row['average_rating'], 2),
                'count'   => (int) $row['review_count'],
            ];
        }

        foreach ($rows as $row) {
            $id = (int) $row['product_id'];

            $row['product_id']       = $id;
            $row['price']            = (float) $row['price'];
            $row['stock_quantity']   = (int) $row['stock_quantity'];
            $row['average_rating']   = $ratings[$id]['average'] ?? 0.0;
            $row['review_count']     = $ratings[$id]['count'] ?? 0;
            $row['collection_slugs'] = $this->collectionSlugs[$id] ?? [];

            $this->products->add($row);
        }
    }

    /** Case-insensitive membership test for a product's collection slugs. */
    private static function hasCollection(array $slugs, string $needle): bool
    {
        foreach ($slugs as $slug) {
            if (strcasecmp((string) $slug, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /** Case-insensitive substring check that works with or without mbstring. */
    private static function containsIgnoreCase(string $haystack, string $needle): bool
    {
        if (function_exists('mb_stripos')) {
            return mb_stripos($haystack, $needle, 0, 'UTF-8') !== false;
        }

        return stripos($haystack, $needle) !== false;
    }

    /** Name A-Z, then product id, so quicksort ties never depend on input order. */
    private static function compareNames(array $a, array $b): int
    {
        $name = strcasecmp((string) $a['name'], (string) $b['name']);
        return $name !== 0 ? $name : ((int) $a['product_id'] <=> (int) $b['product_id']);
    }

    /** Valid numeric filter value, or null when the input is empty/invalid. */
    private static function numberOrNull(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric(trim($value))) {
            return (float) trim($value);
        }

        return null;
    }
}
