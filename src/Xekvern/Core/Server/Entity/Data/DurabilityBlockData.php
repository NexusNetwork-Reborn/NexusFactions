<?php

namespace Xekvern\Core\Server\Entity\Data;

use cosmicpe\blockdata\BlockData;
use pocketmine\nbt\tag\CompoundTag;

final class DurabilityBlockData implements BlockData {
    public function __construct(
        private int $durability = 0,
    ) { }

    public function getDurability(): int {
        return $this->durability;
    }

    public function setDurability(int $durability): self {
        $this->durability = $durability;
        return $this;
    }

    public static function nbtDeserialize(CompoundTag $nbt): BlockData {
        return new self(
            $nbt->getInt("durability", 0)
        );
    }

    public function nbtSerialize(): CompoundTag {
        return CompoundTag::create()
            ->setInt("durability", $this->durability);
    }
}