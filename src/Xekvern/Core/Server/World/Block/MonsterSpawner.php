<?php

namespace Xekvern\Core\Server\World\Block;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\MonsterSpawner as PMMonsterSpawner;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\BlockTransaction;
use pocketmine\world\World;
use Xekvern\Core\Server\Entity\EntityHandler;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Tile\MonsterSpawnerTile;
use Xekvern\Core\Server\World\WorldHandler;
use Xekvern\Core\Translation\Translation;

class MonsterSpawner extends PMMonsterSpawner {
    private const DEFAULT_LEGACY_ENTITY_TYPE_ID = -1;

    private string $entityTypeId = EntityIds::PHANTOM;
    private int $legacyEntityTypeId = self::DEFAULT_LEGACY_ENTITY_TYPE_ID;

    public function __construct() {
        parent::__construct(
            new BlockIdentifier(BlockTypeIds::MONSTER_SPAWNER, MonsterSpawnerTile::class),
            "Monster Spawner",
            new BlockTypeInfo(BlockBreakInfo::pickaxe(3)));
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool {
        $parent = parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
        $tile = $this->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof MonsterSpawnerTile) return false;
        if ($item->getNamedTag()->getTag("EntityId") !== null &&
            $item->getNamedTag()->getString("EntityId") === $tile->getEntityTypeId() &&
            $tile->getStacked() < WorldHandler::MAX_SPAWNER_STACK) {
            $add = 1;
            $stack = $tile->getStacked();
            if ($player->isSneaking()) {
                $add = $item->getCount();
            }
            if ($add + $stack > WorldHandler::MAX_SPAWNER_STACK) {
                $add = WorldHandler::MAX_SPAWNER_STACK - $stack;
            }
            if ($stack < WorldHandler::MAX_SPAWNER_STACK) {
                $stack += $add;
                $tile->setStacked($stack);
                $player->getInventory()->setItemInHand($item->setCount($item->getCount() - $add));
            }
            $player->sendMessage(Translation::AQUA . "You have stacked " . TextFormat::GREEN . "+" . $add . TextFormat::GRAY . " to this spawner.");
            return true;
        }
        $player->sendMessage(Translation::ORANGE . "There are " . TextFormat::YELLOW . "x" . $tile->getStacked() . TextFormat::GRAY . " stacked in this spawner.");
        $player->sendMessage(TextFormat::YELLOW . "(This spawner will cost atleast $" . number_format((25/100) * WorldHandler::getSpawnerValue($tile->getEntityTypeId()) * $tile->getStacked()) . " to break.)");
        return $parent;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        $namedTag = $item->getNamedTag();
        if ($namedTag->getTag("EntityId") !== null) {
            $this->entityTypeId = $namedTag->getString("EntityId");
            $this->legacyEntityTypeId = EntityHandler::LEGACY_ID_MAP[$namedTag->getString("EntityId")];
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onScheduledUpdate(): void {
        $tile = $this->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof MonsterSpawnerTile) return;
        $tile->onUpdate();
        $this->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 3);
    }

    public function getWorld(): World {
        return $this->getPosition()->getWorld();
    }

    public function readStateFromWorld(): Block {
        $parent = parent::readStateFromWorld();
        $tile = $this->getWorld()->getTile($this->position);
        if (!$tile instanceof MonsterSpawnerTile) {
            return $parent;
        }

        if ($this->legacyEntityTypeId == self::DEFAULT_LEGACY_ENTITY_TYPE_ID) {
            return $this;
        }

        $this->entityTypeId = $tile->getEntityTypeId();
        $this->legacyEntityTypeId = EntityHandler::LEGACY_ID_MAP[$tile->getEntityTypeId()];

        return $parent;
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getWorld()->getTile($this->position);
        if (!$tile instanceof MonsterSpawnerTile) {
            return;
        }

        $tile->setEntityTypeId($this->entityTypeId);
        $tile->setLegacyEntityTypeId($this->legacyEntityTypeId);
    }
}