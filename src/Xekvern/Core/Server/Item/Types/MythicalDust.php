<?php

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\item\VanillaItems;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;

class MythicalDust extends CustomItem {

    const GAIN = "Gain";

    /**
     * MythicalDust constructor.
     *
     * @param int $success
     */
    public function __construct(int $success) {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "Mythical Dust";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Move this on top of an enchantment book to increase";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "its success rate by " .TextFormat::BOLD . TextFormat::GREEN . "$success%" . TextFormat::RESET . TextFormat::GRAY . ".";
        parent::__construct(VanillaItems::GLOWSTONE_DUST(), $customName, $lore, [], [
            self::GAIN => new IntTag($success)
        ]);
    }
}