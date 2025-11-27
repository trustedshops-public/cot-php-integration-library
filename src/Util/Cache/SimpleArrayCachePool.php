<?php

declare(strict_types=1);

namespace TRSTD\COT\Util\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Simple array-based cache pool implementation for maximum compatibility
 * This cache doesn't persist between requests but ensures PSR-6 compliance
 */
class SimpleArrayCachePool implements CacheItemPoolInterface
{
    /** @var array<string, SimpleArrayCacheItem> */
    private array $items = [];
    /** @var array<int, CacheItemInterface> */
    private array $deferredItems = [];

    public function getItem(string $key)
    {
        $this->validateKey($key);

        if (isset($this->items[$key])) {
            $item = $this->items[$key];
            if (!$item->isExpired()) {
                return $item;
            }
            unset($this->items[$key]);
        }

        return new SimpleArrayCacheItem($key, null, false);
    }

    /**
     * @param array<string> $keys
     * @return array<string, CacheItemInterface>
     */
    public function getItems(array $keys = [])
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }
        return $items;
    }

    public function hasItem(string $key)
    {
        $this->validateKey($key);

        if (isset($this->items[$key])) {
            $item = $this->items[$key];
            if (!$item->isExpired()) {
                return true;
            }
            unset($this->items[$key]);
        }

        return false;
    }

    public function clear()
    {
        $this->items = [];
        $this->deferredItems = [];
        return true;
    }

    public function deleteItem(string $key)
    {
        $this->validateKey($key);
        unset($this->items[$key]);
        return true;
    }

    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
        return true;
    }

    public function save(CacheItemInterface $item)
    {
        if ($item instanceof SimpleArrayCacheItem) {
            $this->items[$item->getKey()] = $item;
            return true;
        }
        return false;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferredItems[] = $item;
        return true;
    }

    public function commit()
    {
        foreach ($this->deferredItems as $item) {
            $this->save($item);
        }
        $this->deferredItems = [];
        return true;
    }

    private function validateKey(string $key): void
    {
        if (!is_string($key) || $key === '') {
            throw new \InvalidArgumentException('Cache key must be a non-empty string');
        }

        if (preg_match('/[{}()\\/\\\\@:]/', $key)) {
            throw new \InvalidArgumentException('Cache key contains invalid characters');
        }
    }
}
