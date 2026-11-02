<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register(array $data)
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$data['email'], $data['username']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'That email or username is already registered.'];
        }

        try {
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
                'INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$data['username'], $data['email'], $hash, $data['full_name']]);
            return ['success' => true, 'user_id' => (int) $this->db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'That email or username is already registered.'];
        }
    }

    public function login(string $identifier, string $password)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateProfile(int $id, array $data)
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET full_name = ?, phone = ?, address = ?, city = ?, postal_code = ?, country = ? WHERE id = ?'
        );
        return $stmt->execute([
            $data['full_name'], $data['phone'], $data['address'],
            $data['city'], $data['postal_code'], $data['country'], $id,
        ]);
    }
}
