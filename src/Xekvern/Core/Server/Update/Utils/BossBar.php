<?php

namespace Xekvern\Core\Server\Update\Utils;

use pocketmine\entity\Attribute as Att;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;

class BossBar {

    private int|float $healthPercent = 100;
    private string $title = "";
    private int $uniqueId;
    private bool $spawned = false;

    public function __construct(protected Player $player) {
    }

    public function update(string $title, float $healthPercent) {
        $this->title = $title;
        if ($healthPercent > 100) {
            $healthPercent = 100;
        }
        $this->healthPercent = $healthPercent;
    }

    public function isSpawned(): bool {
        return $this->spawned;
    }

    public function despawn(): void {
        $pk = new RemoveActorPacket();
        $pk->actorUniqueId = $this->uniqueId;
        $this->player->getNetworkSession()->sendDataPacket($pk);
        $this->spawned = false;
    }   

    public function spawn() {
        if (!$this->spawned) {
            $this->uniqueId = Entity::nextRuntimeId();
            $metadata = new EntityMetadataCollection();
            $metadata->setGenericFlag(EntityMetadataFlags::FIRE_IMMUNE, true);
            $metadata->setGenericFlag(EntityMetadataFlags::SILENT, true);
            $metadata->setGenericFlag(EntityMetadataFlags::INVISIBLE, true);
            $metadata->setGenericFlag(EntityMetadataFlags::NO_AI, true);
            $metadata->setString(EntityMetadataProperties::NAMETAG, '');
            $metadata->setFloat(EntityMetadataProperties::SCALE, 0.0);
            $metadata->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
            $metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
            $metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
            $addActorPk = AddActorPacket::create($this->uniqueId, Entity::nextRuntimeId(), EntityIds::ZOMBIE, Vector3::zero(), null, 0.0, 0.0, 0.0, 0.0, [new Attribute(Att::HEALTH, 0.0, 100.0, 100.0, 100.0, [])], $metadata->getAll(), new PropertySyncData([], []), []);
            $this->player->getNetworkSession()->sendDataPacket($addActorPk);
            $bossPk = BossEventPacket::show($this->uniqueId, $this->title, $this->healthPercent);
            $this->player->getNetworkSession()->sendDataPacket($bossPk);
        }
    }
}