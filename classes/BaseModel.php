<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Parent class of every model (User, Product, Order, Review, Inquiry, Cart).
 *
 * ABSTRACTION  : abstract class; subclasses must implement table().
 * ENCAPSULATION: the database connection is protected, so only the model classes can touch it.
 * INHERITANCE  : children inherit $db, the constructor and countAll().
 * POLYMORPHISM : children override describe() (and Product overrides countAll()).
 */
abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** The main table this model works with. Every subclass must provide it. */
    abstract protected function table(): string;

    /** Inherited by all models; a child may override it. */
    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ' . $this->table())->fetchColumn();
    }

    /** One-line summary of the model. Subclasses override this with their own wording. */
    public function describe(): string
    {
        return ucfirst($this->table()) . ': ' . $this->countAll() . ' records';
    }
}
