<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Naming note: in the database, `categories` holds the FRANCHISE of a product (Marvel, DC, ...).
 * The wireframe's "Categories" pills (Movies, Anime, Exclusives, Games) live in `collections`,
 * and a product can belong to several of them through `product_collections`.
 */
class Product extends BaseModel
{
    protected function table(): string
    {
        return 'products';
    }

    /**
     * Filterable, sortable, paginated listing.
     * $filters may contain: category (franchise slug), collection (category-pill slug), search,
     * min_price, max_price, stock ('in' | 'out'), sort
     */
    public function getAll(array $filters = [], int $limit = 16, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $sortMap = [
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC, p.product_id DESC',
            'name'       => 'p.name ASC',
        ];
        $orderBy = $sortMap[$filters['sort'] ?? ''] ?? $sortMap['newest'];

        $limit  = max(1, (int) $limit);
        $offset = max(0, (int) $offset);

        $sql = "SELECT p.*, c.category_name, c.slug AS category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE $where
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $sql = "SELECT COUNT(*) AS total
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    private function buildWhere(array $filters): array
    {
        $where  = '1=1';
        $params = [];

        if (!empty($filters['category']) && is_string($filters['category'])) {
            $where .= ' AND c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['collection']) && is_string($filters['collection'])) {
            $where .= ' AND EXISTS (SELECT 1 FROM product_collections pc
                                    JOIN collections col ON col.collection_id = pc.collection_id
                                    WHERE pc.product_id = p.product_id AND col.slug = ?)';
            $params[] = $filters['collection'];
        }
        if (!empty($filters['search']) && is_string($filters['search'])) {
            $where .= ' AND (p.name LIKE ? OR c.category_name LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            array_push($params, $like, $like);
        }
        if (isset($filters['min_price']) && is_scalar($filters['min_price']) && $filters['min_price'] !== '') {
            $where .= ' AND p.price >= ?';
            $params[] = (float) $filters['min_price'];
        }
        if (isset($filters['max_price']) && is_scalar($filters['max_price']) && $filters['max_price'] !== '') {
            $where .= ' AND p.price <= ?';
            $params[] = (float) $filters['max_price'];
        }
        if (($filters['stock'] ?? '') === 'in') {
            $where .= ' AND p.stock_quantity > 0';
        } elseif (($filters['stock'] ?? '') === 'out') {
            $where .= ' AND p.stock_quantity <= 0';
        }

        return [$where, $params];
    }

    public function getBySlug(string $slug): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.category_name, c.slug AS category_slug
             FROM products p LEFT JOIN categories c ON p.category_id = c.category_id
             WHERE p.slug = ?'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.category_name, c.slug AS category_slug
             FROM products p LEFT JOIN categories c ON p.category_id = c.category_id
             WHERE p.product_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Products the admin marked "Show on home page". */
    public function getFeatured(int $limit = 8): array
    {
        $limit = max(1, $limit);
        $stmt = $this->db->prepare(
            "SELECT p.*, c.category_name FROM products p
             LEFT JOIN categories c ON p.category_id = c.category_id
             WHERE p.is_featured = 1
             ORDER BY p.created_at DESC, p.product_id DESC LIMIT $limit"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** "You may also like": same franchise first, then topped up with other pops. */
    public function getRelated(?int $categoryId, int $excludeId, int $limit = 4): array
    {
        $limit = max(1, $limit);
        $rows  = [];
        if ($categoryId) {
            $stmt = $this->db->prepare(
                "SELECT p.*, c.category_name FROM products p
                 LEFT JOIN categories c ON p.category_id = c.category_id
                 WHERE p.category_id = ? AND p.product_id != ?
                 ORDER BY p.created_at DESC, p.product_id DESC LIMIT $limit"
            );
            $stmt->execute([$categoryId, $excludeId]);
            $rows = $stmt->fetchAll();
        }
        if (count($rows) < $limit) {
            $have   = array_merge([$excludeId], array_map('intval', array_column($rows, 'product_id')));
            $marks  = implode(',', array_fill(0, count($have), '?'));
            $need   = $limit - count($rows);
            $stmt   = $this->db->prepare(
                "SELECT p.*, c.category_name FROM products p
                 LEFT JOIN categories c ON p.category_id = c.category_id
                 WHERE p.product_id NOT IN ($marks)
                 ORDER BY p.created_at DESC, p.product_id DESC LIMIT $need"
            );
            $stmt->execute($have);
            $rows = array_merge($rows, $stmt->fetchAll());
        }
        return $rows;
    }

    // ---- Categories pills (Movies, Anime, ...) ----

    public function getCollections(): array
    {
        return $this->db->query('SELECT * FROM collections ORDER BY collection_id ASC')->fetchAll();
    }

    /** @return int[] */
    public function getCollectionIdsFor(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT collection_id FROM product_collections WHERE product_id = ?');
        $stmt->execute([$productId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Replace a product's category pills with exactly $ids (unknown ids are ignored). */
    public function setCollections(int $productId, array $ids): void
    {
        $valid = array_map('intval', array_column($this->getCollections(), 'collection_id'));
        $ids   = array_values(array_unique(array_intersect(array_map('intval', $ids), $valid)));

        $this->db->prepare('DELETE FROM product_collections WHERE product_id = ?')->execute([$productId]);
        $ins = $this->db->prepare('INSERT INTO product_collections (product_id, collection_id) VALUES (?, ?)');
        foreach ($ids as $cid) {
            $ins->execute([$productId, $cid]);
        }
    }

    /** Category pills with a sample photo and product count, for the home carousel. */
    public function getCollectionTiles(): array
    {
        return $this->db->query(
            "SELECT col.collection_id AS id, col.collection_name AS name, col.slug,
                    COUNT(p.product_id) AS product_count,
                    (SELECT p2.image_url FROM product_collections pc2
                       JOIN products p2 ON p2.product_id = pc2.product_id
                      WHERE pc2.collection_id = col.collection_id AND p2.image_url IS NOT NULL AND p2.image_url != ''
                      ORDER BY p2.created_at DESC, p2.product_id DESC LIMIT 1) AS image_url
             FROM collections col
             LEFT JOIN product_collections pc ON pc.collection_id = col.collection_id
             LEFT JOIN products p ON p.product_id = pc.product_id
             GROUP BY col.collection_id, col.collection_name, col.slug
             ORDER BY col.collection_id ASC"
        )->fetchAll();
    }

    /** Franchises with a sample photo and product count, for the second home carousel. */
    public function getFranchiseTiles(): array
    {
        return $this->db->query(
            "SELECT c.category_id AS id, c.category_name AS name, c.slug,
                    COUNT(p.product_id) AS product_count,
                    (SELECT p2.image_url FROM products p2
                      WHERE p2.category_id = c.category_id AND p2.image_url IS NOT NULL AND p2.image_url != ''
                      ORDER BY p2.created_at DESC, p2.product_id DESC LIMIT 1) AS image_url
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.category_id
             GROUP BY c.category_id, c.category_name, c.slug
             ORDER BY c.category_name ASC"
        )->fetchAll();
    }

    // ---- Counts for the admin dashboard ----

    public function countOutOfStock(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM products WHERE stock_quantity <= 0')->fetchColumn();
    }

    // ---- Writing ----

    /**
     * The products table has no UNIQUE index on slug, so uniqueness is enforced here:
     * "cosmic-hulk" becomes "cosmic-hulk-2", "cosmic-hulk-3", ... if it is already taken.
     * $excludeId lets a product keep its own slug while being edited.
     */
    public function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base;
        $n = 2;
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $sql = 'SELECT 1 FROM products WHERE slug = ?';
        $params = [$slug];
        if ($excludeId) {
            $sql .= ' AND product_id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /** Returns the new product id, or 0 on failure. */
    public function create(array $data): int
    {
        try {
            // condition_grade / is_authenticated are no longer part of the admin form;
            // they keep their "not graded" / "not verified" values.
            $stmt = $this->db->prepare(
                'INSERT INTO products
                 (category_id, name, slug, price, stock_quantity, condition_grade, is_authenticated,
                  is_featured, image_url, description, edition)
                 VALUES (?,?,?,?,?,NULL,0,?,?,?,?)'
            );
            $stmt->execute([
                $data['category_id'], $data['name'], $data['slug'], $data['price'], $data['stock_quantity'],
                $data['is_featured'], $data['image_url'], $data['description'], $data['edition'],
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->setCollections($id, $data['collection_ids'] ?? []);
            return $id;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE products SET
                    category_id = ?, name = ?, slug = ?, price = ?, stock_quantity = ?,
                    is_featured = ?, image_url = ?, description = ?, edition = ?
                 WHERE product_id = ?'
            );
            $stmt->execute([
                $data['category_id'], $data['name'], $data['slug'], $data['price'], $data['stock_quantity'],
                $data['is_featured'], $data['image_url'], $data['description'], $data['edition'], $id,
            ]);
            $this->setCollections($id, $data['collection_ids'] ?? []);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Removes the product from carts, category pills and reviews, then deletes it. Because
     * order_items has a foreign key to products, a product that appears in an old order cannot be
     * deleted: this returns false and leaves everything untouched.
     */
    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            $this->db->prepare('DELETE FROM cart_items WHERE product_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM product_collections WHERE product_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM reviews WHERE product_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM products WHERE product_id = ?')->execute([$id]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /** Atomically reduce stock; returns false if not enough stock remained. */
    public function decrementStock(int $id, int $qty): bool
    {
        $stmt = $this->db->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?');
        $stmt->execute([$qty, $id, $qty]);
        return $stmt->rowCount() > 0;
    }

    /** Polymorphism: overrides BaseModel::describe() with product-specific wording. */
    public function describe(): string
    {
        return 'Products: ' . $this->countAll() . ' total, ' . $this->countOutOfStock() . ' out of stock';
    }
}
