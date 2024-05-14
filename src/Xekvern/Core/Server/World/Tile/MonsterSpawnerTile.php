<?php

namespace Xekvern\Core\Server\World\Tile;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\tile\MonsterSpawner as PMSpawnerTile;
use pocketmine\entity\Location;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\world\particle\MobSpawnParticle;
use pocketmine\world\World;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Entity\EntityHandler;
use Xekvern\Core\Server\Entity\Types\Spawner\Blaze;
use Xekvern\Core\Server\Entity\Types\Spawner\Cow;
use Xekvern\Core\Server\Entity\Types\Spawner\IronGolem;
use Xekvern\Core\Server\Entity\Types\Spawner\Pig;
use Xekvern\Core\Server\Entity\Types\Spawner\Squid;
use Xekvern\Core\Server\Entity\Types\Spawner\Zombie;
use Xekvern\Core\Server\Entity\Types\SpawnerEntity;

class MonsterSpawnerTile extends PMSpawnerTile
{

    private const TAG_STACKED = "Stacked"; //TAG_Short
    private const TAG_LEGACY_ENTITY_TYPE_ID = "EntityId"; //TAG_Int
    private const TAG_ENTITY_TYPE_ID = "EntityIdentifier"; //TAG_String

    const SPAWNABLE_BLOCKS = [
        BlockTypeIds::AIR,
        BlockTypeIds::WATER,
        BlockTypeIds::LAVA
    ];

    const CLASS_MAP = [
        EntityIds::BLAZE => Blaze::class,
        EntityIds::COW => Cow::class,
        EntityIds::IRON_GOLEM => IronGolem::class,
        EntityIds::PIG => Pig::class,
        EntityIds::SQUID => Squid::class,
        EntityIds::CREEPER => Zombie::class,
    ];

    const LEGACY_ID_MAP = [
        EntityIds::BLAZE => 43,
        EntityIds::COW => 11,
        EntityIds::IRON_GOLEM => 20,
        EntityIds::PIG => 12,
        EntityIds::SQUID => 17,
        EntityIds::CREEPER => 33,
    ];

    private int $spawnCount = 3;
    private int $stacked = 1;

    private string $entityTypeId = EntityIds::PHANTOM;
    private int $legacyEntityTypeId = -1;

    private int $spawnDelay = self::DEFAULT_MIN_SPAWN_DELAY / 20;
    private int $minSpawnDelay = self::DEFAULT_MIN_SPAWN_DELAY / 20;
    private int $maxSpawnDelay = self::DEFAULT_MAX_SPAWN_DELAY / 20;

    public function getWorld(): World
    {
        return $this->getPosition()->getWorld();
    }

    public function onUpdate(): bool
    {
        $hasPlayer = false;
        if ($this->entityTypeId !== EntityIds::PHANTOM && $this->getWorld()->isChunkLoaded($this->getPosition()->getX() >> 4, $this->getPosition()->getZ() >> 4)) {
            $entityCount = 0;
            foreach ($this->getWorld()->getEntities() as $e) {
                if ($e instanceof NexusPlayer && $e->getPosition()->distance($this->getPosition()) <= 16) {
                    $hasPlayer = true;
                }
                if ($e::getNetworkTypeId() == $this->entityTypeId) {
                    $entityCount++;
                }
            }
        }
        if($hasPlayer == false) {
            return false;
        }
        if ($this->legacyEntityTypeId == -1) {
            if ($this->entityTypeId == ":") {
                $this->entityTypeId = EntityIds::PHANTOM;
                return true;
            }
            $this->legacyEntityTypeId = self::LEGACY_ID_MAP[$this->entityTypeId];
        }

        $this->timings->startTiming();

        if ($this->spawnDelay <= 0) {
            $position = $this->getBlock()->getPosition();
            $entityTypeId = $this->entityTypeId;
            $bb = new AxisAlignedBB($position->getX() - 5, $position->getY() - 1.5, $position->getZ() - 5, $position->getX() + 5, $position->getY() + 1.5, $position->getZ() + 5);
            $bb->expandedCopy(3, 6, 3);
            $success = false;

            foreach ($this->getWorld()->getNearbyEntities($bb) as $entity) {
                if ($entity instanceof SpawnerEntity && $entity::getNetworkTypeId() === $entityTypeId) {
                    $entity->setStack($entity->getStack() + $this->getSpawnCount());
                    $success = true;
                    break;
                }
            }
            if (!$success) {
                $y_value = $this->getWorld()->getBlock($position->add(0, 1, 0))->getTypeId() == BlockTypeIds::AIR ? 1 : -1;
                $pos = $position->add(mt_rand(0, 1), $y_value, mt_rand(0, 1));
                $target = $this->getWorld()->getBlock($pos);

                if (in_array($target->getTypeId(), self::SPAWNABLE_BLOCKS, true)) {
                    $nearest = $this->findNearestEntity($this->getEntityTypeId());
                    if ($nearest !== null) {
                        $nearest->setStack($nearest->getStack() + 1);
                        return true;
                    }
                    $nbt = (new CompoundTag())->setInt("stack", 1);
                    (Nexus::getInstance()->getServerManager()->getEntityHandler()->getEntityFor($entityTypeId, Location::fromObject($pos, $this->getWorld()), $nbt))->spawnToAll();
                    $this->getWorld()->addParticle($pos, new MobSpawnParticle(1, 1));
                    $success = true;
                }
            }

            if ($success) {
                $this->generateRandomDelay();
            }
        } else {
            $this->spawnDelay--;
        }

        $this->timings->stopTiming();
        return true;
    }

    protected function generateRandomDelay(): int
    {
        return ($this->spawnDelay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay));
    }

    public function getSpawnCount(): int
    {
        return $this->spawnCount;
    }

    public function getStacked(): int
    {
        return $this->stacked;
    }

    public function setStacked(int $stacked): void
    {
        $this->stacked = $stacked;
    }

    public function getEntityTypeId(): string
    {
        return $this->entityTypeId;
    }

    public function getLegacyEntityTypeId(): int
    {
        return $this->legacyEntityTypeId;
    }

    public function setEntityTypeId(string $entityTypeId): void
    {
        $this->entityTypeId = $entityTypeId;
    }

    public function setLegacyEntityTypeId(int $legacyEntityTypeId): void
    {
        $this->legacyEntityTypeId = $legacyEntityTypeId;
    }

    public function writeSaveData(CompoundTag $nbt): void
    {
        parent::writeSaveData($nbt);

        $nbt->setString(self::TAG_ENTITY_TYPE_ID, $this->entityTypeId);
        $nbt->setInt(self::TAG_LEGACY_ENTITY_TYPE_ID, $this->legacyEntityTypeId);
        $nbt->setShort(self::TAG_STACKED, $this->stacked);
    }

    public function readSaveData(CompoundTag $nbt): void
    {
        parent::readSaveData($nbt);

        $this->entityTypeId = $nbt->getString(self::TAG_ENTITY_TYPE_ID, $this->entityTypeId);
        $this->legacyEntityTypeId = $nbt->getInt(self::TAG_LEGACY_ENTITY_TYPE_ID, $this->legacyEntityTypeId);
        $this->stacked = $nbt->getShort(self::TAG_STACKED, $this->stacked);
    }

    public function addAdditionalSpawnData(CompoundTag $nbt): void
    {
        $nbt->setString(self::TAG_ENTITY_TYPE_ID, $this->getEntityTypeId());
        $nbt->setInt(self::TAG_LEGACY_ENTITY_TYPE_ID, $this->getLegacyEntityTypeId());
    }

    private function findNearestEntity(string $type): ?SpawnerEntity
    {
        $pos = $this->getBlock()->getPosition();
        foreach ($this->getBlock()->getPosition()->getWorld()->getNearbyEntities(new AxisAlignedBB(
            $pos->x - 25,
            $pos->y - 25,
            $pos->z - 25,
            $pos->x + 25,
            $pos->y + 25,
            $pos->z + 25
        )) as $entity) {
            if ($entity->isAlive() and ($entity instanceof SpawnerEntity)) {
                if ($entity::getNetworkTypeId() === $type) {
                    return $entity;
                }
            }
        }
        return null;
    }
}
