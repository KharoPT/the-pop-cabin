<?php
require_once __DIR__ . '/../config/database.php';

class Cart
{
    private PDO $db;
    private int $cartId;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->cartId = $this->getOrCreateCart();
    }

    private function getOrCreateCart()
    {
        if (isLoggedIn()) {
            $userId = currentUserId();
            $stmt = $this->db->prepare('SELECT id FROM carts WHERE user_id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $cart = $stmt->fetch();

            if ($cart) {
                $this->mergeGuestCart((int) $cart['id']);
                return (int) $cart['id'];
            }

            $stmt = $this->db->prepare('INSERT INTO carts (user_id) VALUES (?)');
            $stmt->execute([$userId]);
            $newCartId = (int) $this->db->lastInsertId();
            $this->mergeGuestCart($newCartId);
            return $newCartId;
        }

        if (empty($_SESSION['guest_cart_session'])) {
            $_SESSION['guest_cart_session'] = bin2hex(random_bytes(16));
        }
        $sessionId = $_SESSION['guest_cart_session'];

        $stmt = $this->db->prepare('SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL LIMIT 1');
        $stmt->execute([$sessionId]);
        $cart = $stmt->fetch();
        if ($cart) {
            return (int) $cart['id'];
        }

        $stmt = $this->db->prepare('INSERT INTO carts (session_id) VALUES (?)');
        $stmt->execute([$sessionId]);
        return (int) $this->db->lastInsertId();
    }

    private function mergeGuestCart(int $userCartId)
    {
        if (empty($_SESSION['guest_cart_session'])) {
            return;
        }
        $sessionId = $_SESSION['guest_cart_session'];

        $stmt = $this->db->prepare('SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL LIMIT 1');
        $stmt->execute([$sessionId]);
        $guestCart = $stmt->fetch();
        if (!$guestCart) {
            unset($_SESSION['guest_cart_session']);
            return;
        }

        $stmt = $this->db->prepare('SELECT product_id, quantity FROM cart_items WHERE cart_id = ?');
        $stmt->execute([$guestCart['id']]);
        foreach ($stmt->fetchAll() as $item) {
            $this->addItem((int) $item['product_id'], (int) $item['quantity'], $userCartId);
        }

        $this->db->prepare('DELETE FROM carts WHERE id = ?')->execute([$guestCart['id']]);
        unset($_SESSION['guest_cart_session']);
    }

    public function addItem(int $productId, int $quantity = 1, ?int $cartId = null)
    {
        $cartId = $cartId ?? $this->cartId;

        $stmt = $this->db->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?');
        $stmt->execute([$cartId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $this->db->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
            return $stmt->execute([$existing['quantity'] + $quantity, $existing['id']]);
        }

        $stmt = $this->db->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)');
        return $stmt->execute([$cartId, $productId, $quantity]);
    }

    public function updateItem(int $cartItemId, int $quantity)
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }
        $stmt = $this->db->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?');
        return $stmt->execute([$quantity, $cartItemId, $this->cartId]);
    }

    public function removeItem(int $cartItemId)
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE id = ? AND cart_id = ?');
        return $stmt->execute([$cartItemId, $this->cartId]);
    }

    public function getItems()
    {
        $stmt = $this->db->prepare(
            'SELECT ci.id AS cart_item_id, ci.quantity,
                    p.id AS product_id, p.name, p.slug, p.price, p.image_url, p.stock_quantity
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.id
             WHERE ci.cart_id = ?
             ORDER BY ci.added_at DESC'
        );
        $stmt->execute([$this->cartId]);
        return $stmt->fetchAll();
    }

    public function getTotal()
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getItemCount()
    {
        $stmt = $this->db->prepare('SELECT SUM(quantity) AS cnt FROM cart_items WHERE cart_id = ?');
        $stmt->execute([$this->cartId]);
        return (int) ($stmt->fetch()['cnt'] ?? 0);
    }

    public function clear()
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = ?');
        return $stmt->execute([$this->cartId]);
    }

    public function getCartId()
    {
        return $this->cartId;
    }
}
