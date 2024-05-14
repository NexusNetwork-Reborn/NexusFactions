<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Types;

use pocketmine\nbt\tag\CompoundTag;

class PrimedTNT extends \pocketmine\entity\object\PrimedTNT
{

    /** @var int */
    private $force = 4;

    protected function initEntity(CompoundTag $tag): void
    {
        parent::initEntity($tag);
        if (!$this->saveNBT()->getTag("Force") === null) {
            $this->force = $this->saveNBT()->getShort("Force");
        } else {
            $this->force = 4;
        }
    }
}
