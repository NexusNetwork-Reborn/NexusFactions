<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\ItemHandler;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;

class EnchantmentCrystal extends CustomItem {

    const ENCHANT = "Enchant";

    /**
     * EnchantmentCrystal constructor.
     *
     * @param Enchantment $enchantment
     * @param int $level
     * @param int $success
     */
    public function __construct(Enchantment $enchantment) {
        $customName = TextFormat::RESET . TextFormat::RESET . ItemHandler::getEnchantmentFormat($enchantment, TextFormat::LIGHT_PURPLE, true) . " Crystal";
        $lore = [];
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Drag n' Drop to on top of an item with";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "the same enchant to upgrade it and use ";
        $lore[] = TextFormat::RESET . TextFormat::AQUA . "/ceinfo" . TextFormat::GRAY . " to view its info.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "NOTICE: " . TextFormat::RESET . TextFormat::GRAY . "This cannot add an enchantment";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "to an item, use" . TextFormat::AQUA . " Enchantment Book " . TextFormat::GRAY . "to apply one.";
        parent::__construct(ExtraVanillaItems::END_CRYSTAL(), $customName, $lore, [], [
            self::ENCHANT => new IntTag(EnchantmentIdMap::getInstance()->toId($enchantment)),
            "UniqueId" => new StringTag(uniqid())
        ]);
    }
}