<?php

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\item\VanillaItems;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;

class EnchantmentRemover extends CustomItem {

    const SUCCESS_PERCENTAGE = "SuccessPercentage";

    /**
     * EnchantmentRemover constructor.
     *
     * @param int $success
     */
    public function __construct(int $success) {
        $customName = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "Enchantment Remover";
        $fail = 100 - $success;
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "$success%" . " Success Rate";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "$fail%" . " Fail Rate";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "USE: " . TextFormat::RESET . TextFormat::GRAY . "Bring this item to the" . TextFormat::WHITE . " Alchemist " . TextFormat::GRAY . " to remove";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "your enchantment on your item.";
        parent::__construct(VanillaItems::SUGAR(), $customName, $lore, [], [
            self::SUCCESS_PERCENTAGE => new IntTag($success)
        ]);
    }
}