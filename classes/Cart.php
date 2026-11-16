<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * A cart is always stored in the database (carts / cart_items), never in the session itself.
 *
 * Both carts.user_id and carts.session_id are NOT NULL, so every cart row stores both:
 *   - session_id : a random token kept in $_SESSION['guest_cart_session'] (identifies this browser)
 *   - user_id    : 0 while the shopper is a guest, their real user_id once they are logged in
 *
 * When a guest logs in, their cart is simply "claimed" (user_id set to their id). If they already
 * had a cart from an earlier visit, the guest items are merged into it and the guest cart is removed.
 */
class Cart extends BaseModel
{
    private int $cartId;

    protected function table(): string
    {
        return 'carts';
    }

    public function __construct()
    {
        parent::__construct();
        $this->cartId = $this->getOrCreateCart();
    }

    private function sessionToken(): string
    {
        if (empty($_SESSION['guest_cart_session'])) {
            $_SESSION['guest_cart_session'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['guest_cart_session'];
    }

    private function getOrCreateCart(): int
    {
        $token = $this->sessionToken();

        // The cart this browser started as a guest (user_id = 0), if any.
        $stmt = $this->db->prepare('SELECT cart_id FROM carts WHERE session_id = ? AND user_id = 0 LIMIT 1');
        $stmt->execute([$token]);
        $guestCartId = $stmt->fetchColumn();

        if (!isLoggedIn()) {
            if ($guestCartId) {
                return (int) $guestCartId;
            }
            $stmt = $this->db->prepare('INSERT INTO carts (user_id, session_id) VALUES (0, ?)');
            $stmt->execute([$token]);
            return (int) $this->db->lastInsertId();
        }

        $userId = currentUserId();
        $stmt = $this->db->prepare('SELECT cart_id FROM carts WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $userCartId = $stmt->fetchColumn();

        if ($userCartId) {
            if ($guestCartId) {
                $this->mergeInto((int) $guestCartId, (int) $userCartId);
            }
            return (int) $userCartId;
        }

        if ($guestCartId) {
            // First time this user has a cart: they simply take over the guest cart.
            $stmt = $this->db->prepare('UPDATE carts SET user_id = ? WHERE cart_id = ?');
            $stmt->execute([$userId, $guestCartId]);
            return (int) $guestCartId;
        }

        $stmt = $this->db->prepare('INSERT INTO carts (user_id, session_id) VALUES (?, ?)');
        $stmt->execute([$userId, $token]);
        return (int) $this->db->lastInsertId();
    }

    /** Move every item from one cart into another (adding quantities), then delete the emptied cart. */
    private function mergeInto(int $fromCartId, int $toCartId): void
    {
        $stmt = $this->db->prepare('SELECT product_id, quantity FROM cart_items WHERE cart_id = ?');
        $stmt->execute([$fromCartId]);
        foreach ($stmt->fetchAll() as $item) {
            $this->addItem((int) $item['product_id'], (int) $item['quantity'], $toCartId);
        }

        $this->db->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$fromCartId]);
        $this->db->prepare('DELETE FROM carts WHERE cart_id = ?')->execute([$fromCartId]);
    }

    public function addItem(int $productId, int $quantity = 1, ?int $cartId = null): bool
    {
        $cartId = $cartId ?? $this->cartId;

        $stmt = $this->db->prepare('SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?');
        $stmt->execute([$cartId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $this->db->prepare('UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?');
            return $stmt->execute([$existing['quantity'] + $quantity, $existing['cart_item_id']]);
        }

        $stmt = $this->db->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)');
        return $stmt->execute([$cartId, $productId, $quantity]);
    }

    public function updateItem(int $cartItemId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }
        $stmt = $this->db->prepare('UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?');
        return $stmt->execute([$quantity, $cartItemId, $this->cartId]);
    }

    public function removeItem(int $cartItemId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id = ?');
        return $stmt->execute([$cartItemId, $this->cartId]);
    }

    public function getItems(): array
    {
        $stmt = $this->db->prepare(
            'SELECT ci.cart_item_id, ci.quantity,
                    p.product_id, p.name, p.slug, p.price, p.image_url, p.stock_quantity
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.product_id
             WHERE ci.cart_id = ?
             ORDER BY ci.added_at DESC, ci.cart_item_id DESC'
        );
        $stmt->execute([$this->cartId]);
        return $stmt->fetchAll();
    }

    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getItemCount(): int
    {
        $stmt = $this->db->prepare('SELECT SUM(quantity) AS cnt FROM cart_items WHERE cart_id = ?');
        $stmt->execute([$this->cartId]);
        return (int) ($stmt->fetch()['cnt'] ?? 0);
    }

    public function clear(): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = ?');
        return $stmt->execute([$this->cartId]);
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }
}
