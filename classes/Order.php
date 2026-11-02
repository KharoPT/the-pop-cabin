<?php
require_once __DIR__ . '/../config/database.php';

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $userId, array $cartItems, array $shipping)
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
                 (order_number, user_id, status, subtotal, shipping_fee, total_amount,
                  shipping_name, shipping_address, shipping_city, shipping_postal_code, shipping_country, payment_method)
                 VALUES (?, ?, "pending", ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderNumber, $userId, $subtotal, $shippingFee, $total,
                $shipping['name'], $shipping['address'], $shipping['city'],
                $shipping['postal_code'], $shipping['country'], $shipping['payment_method'],
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStmt  = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)'
            );
            $stockStmt = $this->db->prepare(
                'UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?'
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

    public function getByOrderNumber(string $orderNumber, ?int $userId = null)
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

    public function getItemsByOrderId(int $orderId)
    {
        $stmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getByUserId(int $userId)
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAll(int $limit = 50, int $offset = 0)
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);
        $stmt = $this->db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $orderId, string $status)
    {
        $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $orderId]);
    }
}
