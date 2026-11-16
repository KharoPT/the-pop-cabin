<?php
require_once __DIR__ . '/BaseModel.php';

class Order extends BaseModel
{
    protected function table(): string
    {
        return 'orders';
    }

    /**
     * Create an order from the given cart line items inside a transaction:
     * writes the order + order_items and decrements stock atomically, rolling the
     * entire order back if any line item no longer has enough stock available.
     */
    public function create(int $userId, array $cartItems, array $shipping): array
    {
        $this->db->beginTransaction();
        try {
            $subtotal = 0.0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shippingFee = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : FLAT_SHIPPING_FEE;
            $total = $subtotal + $shippingFee;
            $orderNumber = 'RR-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

            $stmt = $this->db->prepare(
                'INSERT INTO orders
                 (order_number, user_id, order_status, subtotal, shipping_fee, total_amount,
                  shipping_name, shipping_address, shipping_city, shipping_postal_code, shipping_country)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderNumber, $userId, 'pending', $subtotal, $shippingFee, $total,
                $shipping['name'], $shipping['address'], $shipping['city'],
                $shipping['postal_code'], $shipping['country'],
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStmt  = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)'
            );
            $stockStmt = $this->db->prepare(
                'UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?'
            );

            foreach ($cartItems as $item) {
                $itemStmt->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
                $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                if ($stockStmt->rowCount() === 0) {
                    throw new RuntimeException('"' . $item['name'] . '" no longer has enough stock available.');
                }
            }

            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
        } catch (Throwable $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getByOrderNumber(string $orderNumber, ?int $userId = null): array|false
    {
        if ($userId !== null) {
            $stmt = $this->db->prepare('SELECT * FROM orders WHERE order_number = ? AND user_id = ?');
            $stmt->execute([$orderNumber, $userId]);
        } else {
            $stmt = $this->db->prepare('SELECT * FROM orders WHERE order_number = ?');
            $stmt->execute([$orderNumber]);
        }
        return $stmt->fetch();
    }

    public function getItemsByOrderId(int $orderId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    // "Newest first" means highest order_id first.
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY order_id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);
        $stmt = $this->db->prepare("SELECT * FROM orders ORDER BY order_id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Reservations that are still pending, newest first, with customer name and item count. */
    public function getPending(int $limit = 3): array
    {
        $limit = max(1, $limit);
        return $this->db->query(
            "SELECT o.*, COALESCE(SUM(oi.quantity), 0) AS item_count
             FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.order_id
             WHERE o.order_status IS NULL OR o.order_status = 'pending'
             GROUP BY o.order_id ORDER BY o.order_id DESC LIMIT $limit"
        )->fetchAll();
    }

    public function countPending(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM orders WHERE order_status IS NULL OR order_status = 'pending'")->fetchColumn();
    }

    /** Everything, newest first, with item counts (admin Reservations list). */
    public function getAllWithCounts(int $limit = 50, int $offset = 0): array
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);
        return $this->db->query(
            "SELECT o.*, COALESCE(SUM(oi.quantity), 0) AS item_count
             FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.order_id
             GROUP BY o.order_id ORDER BY o.order_id DESC LIMIT $limit OFFSET $offset"
        )->fetchAll();
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE orders SET order_status = ? WHERE order_id = ?');
        return $stmt->execute([$status, $orderId]);
    }

    /** Polymorphism: overrides BaseModel::describe() with order-specific wording. */
    public function describe(): string
    {
        return 'Orders: ' . $this->countAll() . ' total, ' . $this->countPending() . ' pending';
    }
}
