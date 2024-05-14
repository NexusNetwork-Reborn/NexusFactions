<?php

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class TNTLauncher extends CustomItem {

    const USES = "Uses";
    const TIER = "Tier";
    const TYPE = "TNT";
    const RANGE = "Mid";

    /**
     * TNTLauncher constructor.
     *
     * @param int $tier
     * @param int $uses
     */
    public function __construct(int $tier, int $uses, string $type, string $range) {
        $customName = TextFormat::RESET . TextFormat::DARK_RED . TextFormat::BOLD . "TNT Launcher" . TextFormat::RESET . TextFormat::GRAY . " (Right-Click)";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use this and fire " . TextFormat::RED . "TNT" . TextFormat::GRAY . " where";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "you are pointing on and the greater the";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "tier, the large the radius!.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Uses: " . TextFormat::RED . number_format((int)$uses);
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Tier: " . TextFormat::RED . $tier;
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Range: " . TextFormat::RED . $range;
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "TNT Required";
        $lore[] = TextFormat::RESET . TextFormat::WHITE . ItemHandler::getFuelAmountByTier($tier, "tnt") . TextFormat::GRAY . " TNT.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "USE: " . TextFormat::RESET . TextFormat::WHITE . "Right-Click" . TextFormat::GRAY . " to fire";
        parent::__construct(VanillaItems::STICK(), $customName, $lore, [],
        [
            self::USES => new IntTag($uses),
            self::TIER => new IntTag($tier),
            self::TYPE => new StringTag($type),
            self::RANGE => new StringTag($range)
        ]);
    }
}