<?php

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class SellWand extends CustomItem {

    const SELL_WAND = "SellWand";

    /**
     * SellWand constructor.
     *
     * @param int $uses
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "Sell Wand";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Tap a chest to sell all its contents.";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use /swuses to check how many uses you have.";
        parent::__construct(VanillaItems::BLAZE_ROD(), $customName, $lore, [], [
            self::SELL_WAND => new StringTag(self::SELL_WAND)
        ]);
    }
}