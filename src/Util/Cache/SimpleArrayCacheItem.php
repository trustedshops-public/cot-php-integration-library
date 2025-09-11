<?php

declare(strict_types=1);

namespace TRSTD\COT\Util\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Simple array-based cache item implementation for maximum compatibility
 */
class SimpleArrayCacheItem implements CacheItemInterface
{
    private string $key;
    private mixed $value;
    private bool $isHit;
    private ?\DateTimeInterface $expiration;

    public function __construct(string $key, mixed $value = null, bool $isHit = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
        $this->expiration = null;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->isHit ? $this->value : null;
    }

    public function isHit(): bool
    {
        return $this->isHit && !$this->isExpired();
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->isHit = true;
        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;
        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        if ($time === null) {
            $this->expiration = null;
        } elseif ($time instanceof \DateInterval) {
            $this->expiration = (new \DateTime())->add($time);
        } else {
            $this->expiration = new \DateTime('@' . (time() + (int) $time));
        }
        return $this;
    }

    /**
     * Check if the item has expired
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiration !== null && $this->expiration <= new \DateTime();
    }
}
