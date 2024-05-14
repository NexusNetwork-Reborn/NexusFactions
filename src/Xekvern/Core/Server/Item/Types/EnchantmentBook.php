<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\ItemHandler;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class EnchantmentBook extends CustomItem {

    const ENCHANT = "Enchant";
    const SUCCESS = "Success";

    /**
     * EnchantmentBook constructor.
     *
     * @param Enchantment $enchantment
     * @param int $level
     * @param int $success
     */
    public function __construct(Enchantment $enchantment, int $success) {
        $fail = 100 - $success;
        $customName = TextFormat::RESET . ItemHandler::getEnchantmentFormat($enchantment, TextFormat::AQUA, true) . " Book";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "$success%" . " Success Rate";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "$fail%" . " Fail Rate";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Type: " . TextFormat::BOLD . TextFormat::AQUA . ItemHandler::flagToString($enchantment->getPrimaryItemFlags());
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Rarity: " . TextFormat::BOLD . TextFormat::AQUA . ItemHandler::rarityToString($enchantment->getRarity());
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Max Level: " . TextFormat::BOLD . TextFormat::AQUA . $enchantment->getMaxLevel();
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::AQUA . "Hint: " . TextFormat::GRAY . "Drag n' Drop onto an item to enchant";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "and use " . TextFormat::AQUA . "/ceinfo" . TextFormat::GRAY . " to view its info.";
        parent::__construct(VanillaItems::ENCHANTED_BOOK(), $customName, $lore, [], [
            self::ENCHANT => new IntTag(EnchantmentIdMap::getInstance()->toId($enchantment)),
            self::SUCCESS => new IntTag($success),
            "UniqueId" => new StringTag(uniqid())
        ]);
    }
}