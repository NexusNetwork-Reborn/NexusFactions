<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\WorldHandler;

class MonsterSpawner extends CustomItem {

    const MONSTER_SPAWNER = "Monster Spawner";

    private static $entityTypeId; 

    /**
     * MonsterSpawner constructor.
     */
    public function __construct(protected string $entity) {
        $name = explode(":", $entity);
        $name = end($name);
        $name = ucwords(str_replace("_", " ", $name));
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . $name . " Spawner";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Place this spawner in a faction";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "claim for a higher spawn rate.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Wealth Value: " . TextFormat::BOLD . TextFormat::GOLD . "$" . number_format(WorldHandler::getSpawnerValue($entity));
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Cost to Break: " . TextFormat::BOLD . TextFormat::GOLD . "$" . number_format((25/100) * WorldHandler::getSpawnerValue($entity));
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "  (25% of its Value Cost)";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "NOTICE: " . TextFormat::RESET . TextFormat::GRAY . "Once placed you can only";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "break this with a" . TextFormat::WHITE . " Silk Touch " . TextFormat::GRAY . "pickaxe.";
        self::$entityTypeId = $entity;
        parent::__construct(ExtraVanillaItems::MONSTER_SPAWNER()->asItem(), $customName, $lore, [], [
            self::MONSTER_SPAWNER => new StringTag(self::MONSTER_SPAWNER),
            "EntityId" => new StringTag(self::$entityTypeId)
        ]);
    }
}   