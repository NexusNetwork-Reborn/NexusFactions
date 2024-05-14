<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\World\WorldHandler;

class OreGenerator extends CustomItem {

    const ORE_GENERATOR = "Ore Generator";

    /**
     * OreGenerator constructor.
     */
    public function __construct(DyeColor $color) {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_RED . WorldHandler::getGeneratorOreByType($color)->getName() . " Generator";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Place this generator in a faction";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "claim to start ore generation.";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Wealth Value: " . TextFormat::BOLD . TextFormat::GOLD . "$" . number_format(WorldHandler::getGeneratorValue($color));
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Cost to Break: " . TextFormat::BOLD . TextFormat::GOLD . "$" . number_format((25/100) * WorldHandler::getGeneratorValue($color));
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "  (25% of its Value Cost)";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . "TYPE: " . TextFormat::RESET . TextFormat::GRAY . WorldHandler::getGeneratorTypeString(WorldHandler::getGeneratorType($color));
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "NOTICE: " . TextFormat::RESET . TextFormat::GRAY . "You cannot place this";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "block above " . TextFormat::WHITE . " 255 YAW";
        parent::__construct(VanillaBlocks::GLAZED_TERRACOTTA()->setColor($color)->asItem(), $customName, $lore, [], []);
    }
}   