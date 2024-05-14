<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit\Types\Sacred;

use pocketmine\block\VanillaBlocks;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Kit\SacredKit;

class Overlord extends SacredKit
{

    /**
     * Overlord constructor.
     */
    public function __construct()
    {
        $name = TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_RED . "Overlord " . TextFormat::RESET . TextFormat::RED;
        $items = [
            (new CustomItem(VanillaItems::DIAMOND_HELMET(), $name . "Helmet", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 2),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 2),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_CHESTPLATE(), $name . "Chestplate", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 2),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 2),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_LEGGINGS(), $name . "Leggings", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 2),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 2),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_BOOTS(), $name . "Boots", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 2),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 2),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::QUICKENING), 1),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_SWORD(), $name . "Sword", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 4),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::BLEED), 2),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::ANNIHILATION), 2),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::GUILLOTINE), 2),
            ]))->getItemForm(),
            VanillaItems::STEAK()->setCount(64),
            VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(32),
            VanillaItems::GOLDEN_APPLE()->setCount(48),
        ];
        parent::__construct(4, "Overlord", self::RARE, $items, 432000);
    }

    /**
     * @return string
     */
    public function getColoredName(): string
    {
        return TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_RED . "Overlord" . TextFormat::RESET;
    }

}