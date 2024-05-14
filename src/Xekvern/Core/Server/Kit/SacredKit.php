<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit;

abstract class SacredKit extends Kit {

    /** @var int */
    protected $maxTier;

    /**
     * SacredKit constructor.
     *
     * @param int $maxTier
     * @param string $name
     * @param int $rarity
     * @param array $items
     * @param int $cooldown
     */
    public function __construct(int $maxTier, string $name, int $rarity, array $items, int $cooldown) {
        $this->maxTier = $maxTier;
        parent::__construct($name, $rarity, $items, $cooldown);
    }

    /**
     * @return int
     */
    public function getMaxTier(): int {
        return $this->maxTier;
    }
}