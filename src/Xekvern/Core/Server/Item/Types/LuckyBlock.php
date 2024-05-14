<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class LuckyBlock extends CustomItem {

    const LUCK = "Luck";

    /**
     * LuckyBlock constructor.
     *
     * @param int $luck
     */
    public function __construct(int $luck) {
        $customName = TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . "Lucky Block";
        $unluck = 100 - $luck;
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "$luck%" . " of getting something good.";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "$unluck%" . " of getting something bad.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Place and break this block for a surprise.";
        parent::__construct(VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::BLACK())->asItem(), $customName, $lore, [], [
            self::LUCK => new IntTag($luck)
        ]);
    }
}