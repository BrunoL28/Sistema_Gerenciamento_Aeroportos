<?php
include_once "suite.php";
include_once "../classes/HashMap.php";

class IntegerKey implements HashableAndEquatable
{
    private int $key;

    function __construct(int $key)
    {
        $this->key = $key;
    }

    function __toString(): string {
        return "{$this->key}";
    }

    public function eq(Equatable $other): bool
    {
        if (!$other instanceof self) {
            throw new EquatableTypeException();
        }
        return $this->key == $other->key;
    }

    public function hashCode(): int
    {
        return $this->key % 10;
    }
}

class HashMapTestCase extends TestCase
{
    protected function getName(): string
    {
        return "HashMap";
    }

    public function run()
    {
        /**
         * @var HashMap<IntegerKey, int> $map
         */
        $map = new HashMap();
        $key0 = new IntegerKey(0);
        $key1 = new IntegerKey(1);
        $key10 = new IntegerKey(10);
        # Put and get without collisions
        $map->put($key0, 0);
        $map->put($key1, 1);
        $this->checkEq($map->size(), 2);
        $this->checkEq($map->get($key0), 0);
        $this->checkEq($map->get($key1), 1);
        # Updating without collisions
        $map->put($key0, 2);
        $map->put($key1, 3);
        $this->checkEq($map->size(), 2);
        $this->checkEq($map->get($key0), 2);
        $this->checkEq($map->get($key1), 3);
        # Putting with collision
        $map->put($key10, 4);
        $this->checkEq($map->size(), 3);
        $this->checkEq($map->get($key0), 2);
        $this->checkEq($map->get($key1), 3);
        $this->checkEq($map->get($key10), 4);
        # Deleting with collision
        $map->remove($key10);
        $this->checkEq($map->size(), 2);
        $this->checkEq($map->get($key0), 2);
        $this->checkEq($map->get($key1), 3);
        $this->checkEq($map->get($key10), null);
        # Deleting without collision
        $map->remove($key0);
        $this->checkEq($map->size(), 1);
        $this->checkEq($map->get($key0), null);
        $this->checkEq($map->get($key1), 3);
        $this->checkEq($map->get($key10), null);
        # Clearing
        $map->clear();
        $this->checkEq($map->size(), 0);
        $this->checkEq($map->get($key0), null);
        $this->checkEq($map->get($key1), null);
        $this->checkEq($map->get($key10), null);
        # Keys
        $map->put($key0, 0);
        $map->put($key1, 1);
        $this->checkEq($map->keys(), [$key0, $key1]);
        # Values
        $this->checkEq($map->values(), [0, 1]);
        # ContainsKey
        $this->checkEq($map->containsKey($key0), true);
        $this->checkEq($map->containsKey($key1), true);
        $this->checkEq($map->containsKey($key10), false);

    }
}