<?php

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;

class EnchantmentScroll extends CustomItem {

    const ENCHANTMENT_SCROLL = "EnchantmentScroll";
    const SCROLL_AMOUNT = "ScrollAmount";

    /**
     * Soul constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::GREEN . TextFormat::BOLD . "Enchantment Scroll";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Forge this scroll to an item to hack";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "the system and increase enchantment limit by " . TextFormat::GREEN . "+1";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Drag n' Drop to onto an item to apply.";
        parent::__construct(ExtraVanillaItems::ENDER_EYE(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::ENCHANTMENT_SCROLL => new StringTag(self::ENCHANTMENT_SCROLL),
            self::SCROLL_AMOUNT => new IntTag(1),
        ]);
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int {
        return 1;
    }   
}