<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    protected function table(): string
    {
        return 'users';
    }

    public function register(array $data): array
    {
        $stmt = $this->db->prepare('SELECT user_id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$data['email'], $data['username']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'That email or username is already registered.'];
        }

        try {
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            // phone, address, city, postal_code and country are NOT NULL in the users table,
            // so a new account starts with them blank; the customer fills them in later.
            // user_role is not listed here: it defaults to 'customer'.
            $stmt = $this->db->prepare(
                "INSERT INTO users (username, email, password_hash, full_name, phone, address, city, postal_code, country)
                 VALUES (?, ?, ?, ?, '', '', '', '', '')"
            );
            $stmt->execute([$data['username'], $data['email'], $hash, $data['full_name']]);
            return ['success' => true, 'user_id' => (int) $this->db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'That email or username is already registered.'];
        }
    }

    public function login(string $identifier, string $password): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE user_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET full_name = ?, phone = ?, address = ?, city = ?, postal_code = ?, country = ? WHERE user_id = ?'
        );
        return $stmt->execute([
            $data['full_name'], $data['phone'], $data['address'],
            $data['city'], $data['postal_code'], $data['country'], $id,
        ]);
    }

    /** Build a unique username from an email address (the sign-up form no longer asks for one). */
    public function generateUsername(string $email): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode('@', $email)[0]));
        $base = substr($base !== '' ? $base : 'user', 0, 24);
        $name = $base;
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
        for ($i = 0; $i < 20; $i++) {
            $stmt->execute([$name]);
            if (!$stmt->fetchColumn()) {
                return $name;
            }
            $name = $base . random_int(10, 9999);
        }
        return $base . bin2hex(random_bytes(3));
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    }

    public function getAll(int $limit = 20, int $offset = 0): array
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);
        return $this->db->query("SELECT user_id, username, email, full_name, user_role FROM users ORDER BY user_id DESC LIMIT $limit OFFSET $offset")->fetchAll();
    }

    /** Polymorphism: overrides BaseModel::describe() with user-specific wording. */
    public function describe(): string
    {
        return 'Users: ' . $this->countAll() . ' registered accounts';
    }
}
