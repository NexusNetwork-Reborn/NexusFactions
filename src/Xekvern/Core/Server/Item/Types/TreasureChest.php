<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class ToolKit extends CustomItem {

    const TOOL_KIT = "Tool Kit";

    /**
     * ToolKit constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "Treasure Chest";
        $lore = [];
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "!";
        parent::__construct(VanillaBlocks::CHEST()->asItem(), $customName, $lore, [], [
            self::TOOL_KIT => self::TOOL_KIT,
        ]);
    }
}