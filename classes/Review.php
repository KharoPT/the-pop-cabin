<?php
require_once __DIR__ . '/BaseModel.php';

class Review extends BaseModel
{
    protected function table(): string
    {
        return 'reviews';
    }

    /** Newest first. Each row carries the reviewer's display name (already anonymised if asked). */
    public function getByProduct(int $productId, int $limit = 3): array
    {
        $limit = max(1, $limit);
        $stmt = $this->db->prepare(
            "SELECT r.*, u.full_name FROM reviews r
             LEFT JOIN users u ON u.user_id = r.user_id
             WHERE r.product_id = ?
             ORDER BY r.created_at DESC, r.review_id DESC LIMIT $limit"
        );
        $stmt->execute([$productId]);
        return $this->withDisplayNames($stmt->fetchAll());
    }

    /** @return array{average: float, count: int} */
    public function summary(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT COALESCE(AVG(rating),0) a, COUNT(*) c FROM reviews WHERE product_id = ?');
        $stmt->execute([$productId]);
        $row = $stmt->fetch();
        return ['average' => (float) $row['a'], 'count' => (int) $row['c']];
    }

    /** Latest reviews across all pops, for the optional home-page section. */
    public function getLatest(int $limit = 6): array
    {
        $limit = max(1, $limit);
        $rows = $this->db->query(
            "SELECT r.*, u.full_name, p.name AS product_name, p.slug AS product_slug
             FROM reviews r
             LEFT JOIN users u ON u.user_id = r.user_id
             LEFT JOIN products p ON p.product_id = r.product_id
             ORDER BY r.created_at DESC, r.review_id DESC LIMIT $limit"
        )->fetchAll();
        return $this->withDisplayNames($rows);
    }

    public function userHasReviewed(int $productId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$productId, $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(int $productId, int $userId, int $rating, string $comment, bool $anonymous): bool
    {
        if ($rating < 1 || $rating > 5 || $comment === '' || $this->userHasReviewed($productId, $userId)) {
            return false;
        }
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO reviews (product_id, user_id, rating, comment, is_anonymous) VALUES (?,?,?,?,?)'
            );
            return $stmt->execute([$productId, $userId, $rating, $comment, $anonymous ? 1 : 0]);
        } catch (PDOException $e) {
            return false;
        }
    }

    private function withDisplayNames(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['display_name'] = $row['is_anonymous'] ? 'Anonymous' : publicName($row['full_name'] ?? '');
        }
        return $rows;
    }
}
