<?php
/**
 * A hand-written dynamic array for the product catalog.
 *
 * The catalog is loaded once per request into this structure. It starts with a
 * small fixed number of slots and doubles its capacity when full, which is the
 * classic "dynamic array" behaviour:
 *
 *   add()      amortised O(1)  (O(n) only on the occasional resize)
 *   get()/set()          O(1)
 *   removeAt()           O(n)  (the elements after the gap shift left)
 *   toArray()            O(n)
 *
 * PHP arrays are already dynamic; this class keeps the slots/capacity mechanics
 * explicit so the data structure can be demonstrated and tested on its own.
 */
final class DynamicArray implements Countable, IteratorAggregate
{
    /** @var array<int, mixed> Fixed-size slot buffer; null means "unused". */
    private array $slots;

    private int $size = 0;
    private int $capacity;

    public function __construct(int $capacity = 4)
    {
        $this->capacity = max(1, $capacity);
        $this->slots    = array_fill(0, $this->capacity, null);
    }

    /** Build a dynamic array from an ordinary PHP array (used by tests/demos). */
    public static function fromArray(array $items): self
    {
        $array = new self(max(1, count($items)));
        foreach ($items as $item) {
            $array->add($item);
        }
        return $array;
    }

    /** Append an item, doubling the slot buffer when it is full. */
    public function add(mixed $item): void
    {
        if ($this->size === $this->capacity) {
            $this->resize($this->capacity * 2);
        }

        $this->slots[$this->size] = $item;
        $this->size++;
    }

    /** Read the item at an index. O(1). */
    public function get(int $index): mixed
    {
        $this->assertIndex($index);
        return $this->slots[$index];
    }

    /** Replace the item at an index. O(1). */
    public function set(int $index, mixed $item): void
    {
        $this->assertIndex($index);
        $this->slots[$index] = $item;
    }

    /** Remove an item and shift the following items left. O(n). */
    public function removeAt(int $index): mixed
    {
        $this->assertIndex($index);
        $removed = $this->slots[$index];

        for ($i = $index; $i < $this->size - 1; $i++) {
            $this->slots[$i] = $this->slots[$i + 1];
        }

        $this->slots[$this->size - 1] = null;
        $this->size--;

        return $removed;
    }

    public function count(): int
    {
        return $this->size;
    }

    /** Current number of allocated slots, shown by the demo to prove the doubling. */
    public function capacity(): int
    {
        return $this->capacity;
    }

    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /** @return array<int, mixed> */
    public function toArray(): array
    {
        return array_slice($this->slots, 0, $this->size);
    }

    /** Lets foreach ($array as $item) walk only the used slots. */
    public function getIterator(): Traversable
    {
        for ($i = 0; $i < $this->size; $i++) {
            yield $this->slots[$i];
        }
    }

    private function resize(int $newCapacity): void
    {
        $newSlots = array_fill(0, $newCapacity, null);
        for ($i = 0; $i < $this->size; $i++) {
            $newSlots[$i] = $this->slots[$i];
        }

        $this->slots    = $newSlots;
        $this->capacity = $newCapacity;
    }

    private function assertIndex(int $index): void
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException(
                'Index ' . $index . ' is outside the used range 0..' . ($this->size - 1) . '.'
            );
        }
    }
}
