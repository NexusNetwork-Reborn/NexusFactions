<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Entity\Types;

use pocketmine\entity\Attribute;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Utils\Utils;

abstract class SpawnerEntity extends Living
{
    private int $stack = 1;

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1, 1, 1);
    }

    public abstract function getName(): string;

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->stack = $nbt->getInt("stack");
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.05;
    }

    protected function getInitialGravity(): float
    {
        return 0.08;
    }

    public function getStack(): int
    {
        return $this->stack;
    }

    public function setStack(int $stack): void
    {
        $this->stack = $stack;
        $this->updateNameTag();
    }

    public function kill(): void
    {
        if ($this->getStack() > 1) {
            $this->setStack($this->getStack() - 1);
            $this->setHealth($this->getMaxHealth());
           return;
        }
        parent::kill();
    }

    protected function sendSpawnPacket(Player $player): void
    {
        $networkSession = $player->getNetworkSession();
        $networkSession->sendDataPacket(AddActorPacket::create(
            $this->getId(),
            $this->getId(),
            $this->getNetworkTypeId(),
            $this->location->asVector3(),
            $this->getMotion(),
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw, //TODO: head yaw
            $this->location->yaw, //TODO: body yaw (wtf mojang?)
            array_map(function (Attribute $attr): NetworkAttribute {
                return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), []);
            }, $this->attributeMap->getAll()),
            $this->getAllNetworkData(),
            new PropertySyncData([], []),
            []
        ));
        $networkSession->getEntityEventBroadcaster()->onMobArmorChange([$networkSession], $this);
    }

    public function updateNameTag(): void
    {
        $this->setNameTag(TextFormat::YELLOW . TextFormat::BOLD . $this->getName() . " x" . $this->getStack());
    }

    public function saveNBT(): CompoundTag
    {
        return parent::saveNBT()->setInt("stack", $this->getStack());
    }
}
