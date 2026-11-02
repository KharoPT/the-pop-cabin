<?php
require_once __DIR__ . '/../config/database.php';

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(array $filters = [], int $limit = 12, int $offset = 0)
    {
        [$where, $params] = $this->buildWhere($filters);

        $sortMap = [
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC',
            'name'       => 'p.name ASC',
        ];
        $orderBy = $sortMap[$filters['sort'] ?? ''] ?? 'p.created_at DESC';

        $limit  = max(1, (int) $limit);
        $offset = max(0, (int) $offset);

        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $where
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = [])
    {
        [$where, $params] = $this->buildWhere($filters);
        $sql = "SELECT COUNT(*) AS total
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    private function buildWhere(array $filters)
    {
        $where  = '1=1';
        $params = [];

        if (!empty($filters['category'])) {
            $where .= ' AND c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where .= ' AND p.price >= ?';
            $params[] = (float) $filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where .= ' AND p.price <= ?';
            $params[] = (float) $filters['max_price'];
        }
        if (!empty($filters['condition'])) {
            $where .= ' AND p.condition_grade = ?';
            $params[] = $filters['condition'];
        }
        if (!empty($filters['rarity'])) {
            $where .= ' AND p.rarity = ?';
            $params[] = $filters['rarity'];
        }

        return [$where, $params];
    }

    public function getBySlug(string $slug)
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ?'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getFeatured(int $limit = 8)
    {
        $limit = max(1, $limit);
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.is_featured = 1
             ORDER BY p.created_at DESC LIMIT $limit"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRelated(?int $categoryId, int $excludeId, int $limit = 4)
    {
        $limit = max(1, $limit);
        if (!$categoryId) {
            return [];
        }
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT $limit"
        );
        $stmt->execute([$categoryId, $excludeId]);
        return $stmt->fetchAll();
    }

    public function create(array $data)
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO products
                 (category_id, sku, name, slug, description, price, stock_quantity,
                  condition_grade, rarity, era_year, is_authenticated, is_featured, image_url)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            return $stmt->execute([
                $data['category_id'], $data['sku'], $data['name'], $data['slug'], $data['description'],
                $data['price'], $data['stock_quantity'], $data['condition_grade'], $data['rarity'],
                $data['era_year'], $data['is_authenticated'], $data['is_featured'], $data['image_url'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(int $id, array $data)
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE products SET
                    category_id = ?, sku = ?, name = ?, slug = ?, description = ?, price = ?,
                    stock_quantity = ?, condition_grade = ?, rarity = ?, era_year = ?,
                    is_authenticated = ?, is_featured = ?, image_url = ?
                 WHERE id = ?'
            );
            return $stmt->execute([
                $data['category_id'], $data['sku'], $data['name'], $data['slug'], $data['description'],
                $data['price'], $data['stock_quantity'], $data['condition_grade'], $data['rarity'],
                $data['era_year'], $data['is_authenticated'], $data['is_featured'], $data['image_url'], $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id)
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function decrementStock(int $id, int $qty)
    {
        $stmt = $this->db->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?');
        $stmt->execute([$qty, $id, $qty]);
        return $stmt->rowCount() > 0;
    }
}
