<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Types\Spawner;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use Xekvern\Core\Server\Entity\Types\SpawnerEntity;

class Blaze extends SpawnerEntity
{
    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->getNetworkProperties()->setInt(EntityMetadataProperties::VARIANT, 0);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.8, 0.5);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::BLAZE;
    }

    public function getXpDropAmount(): int
    {
        return mt_rand(14, 16);
    }

    public function getName(): string
    {
        return "Blaze";
    }

    public function attack(EntityDamageEvent $source): void
    {
        $immune = [EntityDamageEvent::CAUSE_LAVA, EntityDamageEvent::CAUSE_FIRE, EntityDamageEvent::CAUSE_FIRE_TICK];

        if (in_array($source->getCause(), $immune)) {
            $source->cancel();
            return;
        }
        parent::attack($source);
    }

    public function getDrops(): array
    {
        return [
            VanillaItems::BLAZE_ROD()->setCount(mt_rand(1, 2)),
        ];
    }
}
