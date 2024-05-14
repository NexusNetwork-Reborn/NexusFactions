<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit\Types;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Kit\Kit;

class Spartan extends Kit {

    /**
     * Spartan constructor.
     */
    public function __construct() {
        $name = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Spartan " . TextFormat::RESET . TextFormat::GOLD;
        $items =  [
            (new CustomItem(VanillaItems::DIAMOND_HELMET(), $name . "Helmet", [], [
                new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 7),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 1),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_CHESTPLATE(), $name . "Chestplate", [], [
                new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 7),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 1),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_LEGGINGS(), $name . "Leggings", [], [
                new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 7),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 1),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 1)
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_BOOTS(), $name . "Boots", [], [
                new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 7),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 1),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_SWORD(), $name . "Sword", [], [
                new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 8),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_SHOVEL(), $name . "Shovel", [], [
                new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 6),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5)
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_PICKAXE(), $name . "Pickaxe", [], [
                new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 6),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5)
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_AXE(), $name . "Axe", [], [
                new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 8),
                new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 6),
                new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5)
            ]))->getItemForm(),
            VanillaItems::STEAK()->setCount(64),
            VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(12),
            VanillaItems::GOLDEN_APPLE()->setCount(32),
            VanillaBlocks::OBSIDIAN()->asItem()->setCount(64),
            VanillaBlocks::BEDROCK()->asItem()->setCount(256),
            VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING())->setCount(27)
        ];
        parent::__construct("Spartan", self::COMMON, $items, 43200);
    }

    /**
     * @return string
     */
    public function getColoredName(): string {
        return TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Spartan" . TextFormat::RESET;
    }
}