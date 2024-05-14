<?php

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\StringTag;
use Xekvern\Core\Server\Item\Types\Vanilla\CreeperSpawnEgg;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;

class CreeperEgg extends CustomItem {
    const CREEPER_EGG = "CreeperEgg";

    /**
     * CreeperEgg constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::DARK_GREEN . TextFormat::BOLD . "Creeper Egg";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Spawns a creeper and can be";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "ignited to explode!";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "USE: " . TextFormat::RESET . TextFormat::WHITE . "Place" . TextFormat::GRAY . " to spawn a creeper.";
        parent::__construct(ExtraVanillaItems::CREEPER_SPAWN_EGG(), $customName, $lore, [], [
            self::CREEPER_EGG => new StringTag(self::CREEPER_EGG)
        ]);
    }
}