<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\World\Tile;

use pocketmine\block\tile\Spawnable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class LuckyBlock extends Spawnable {

    const LUCK = "Luck";

    protected int $luck = 0;

    public function __construct(World $level, Vector3 $position) {
        parent::__construct($level, $position);
    }

    public function setLuck(int $luck) {
        $this->luck = $luck;
    }

    public function getLuck(): int {
        return $this->luck;
    }

    public function readSaveData(CompoundTag $nbt): void {
        if($nbt->getTag(self::LUCK) !== null) {
            $this->luck = $nbt->getInt(self::LUCK, 0);
        }
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt(self::LUCK, $this->luck);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setInt(self::LUCK, $this->luck);
    }
}