<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Crate\Types;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\AbilityScroll;
use Xekvern\Core\Server\Item\Types\AttributesBag;
use Xekvern\Core\Server\Item\Types\AttributeShard;
use Xekvern\Core\Server\Item\Types\CosmeticBag;
use Xekvern\Core\Server\Item\Types\CosmeticShard;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Types\OreGenerator;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Utils\GeneratorId;
use Xekvern\Core\Server\World\WorldHandler;

class BossCrate extends Crate
{

    /**
     * BossCrate constructor.
     *
     * @param Position $position
     */
    public function __construct(Position $position)
    {
        parent::__construct(self::BOSS, $position, [
            new Reward("8,000 XP", VanillaItems::PAPER()->setCustomName(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "8,000 XP"), function (NexusPlayer $player): void {
                $player->addXp(8000);
            }, 100),
            new Reward("1,000 Level XP", VanillaItems::PAPER()->setCustomName(TextFormat::BOLD . TextFormat::GREEN . "1,000 Level XP"), function (NexusPlayer $player): void {
                $player->getXpManager()->addXp(1000);
            }, 100),
            new Reward("$100,000", (new MoneyNote(100000))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new MoneyNote(100000))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 65),
            new Reward("$350,000", (new MoneyNote(350000))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new MoneyNote(350000))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 15),
            new Reward("x100 Sell Wand Note", (new SellWandNote(100))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new SellWandNote(100))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 50),
            new Reward("Lapis Lazuli Generator", (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::LAPIS_LAZULI, false)))->getItemForm()->setCount(2), function (NexusPlayer $player): void {
                $items = [
                    (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::LAPIS_LAZULI, false)))->getItemForm()->setCount(2),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Random Enchantment", VanillaItems::ENCHANTED_BOOK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchantment"), function (NexusPlayer $player): void {
                $enchantment = ItemHandler::getRandomEnchantment();
                $items = [
                    (new EnchantmentBook($enchantment, mt_rand(1, 50)))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Random Mythic Enchantment", VanillaItems::ENCHANTED_BOOK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchantment"), function (NexusPlayer $player): void {
                $items = [
                    (new EnchantmentBook(ItemHandler::getRandomEnchantment(Rarity::MYTHIC), mt_rand(1, 100)))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 25),
            new Reward("Random Rare Enchantment", VanillaItems::ENCHANTED_BOOK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchantment"), function (NexusPlayer $player): void {
                $items = [
                    (new EnchantmentBook(ItemHandler::getRandomEnchantment(Rarity::RARE), mt_rand(1, 100)))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 35),
            new Reward("Random Godly Enchantment", VanillaItems::ENCHANTED_BOOK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchantment"), function (NexusPlayer $player): void {
                $items = [
                    (new EnchantmentBook(ItemHandler::getRandomEnchantment(Enchantment::RARITY_GODLY), mt_rand(1, 100)))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 5),
            new Reward("Enchantment Scroll", (new EnchantmentScroll())->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new EnchantmentScroll())->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Attributes Bag", (new AttributesBag())->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new AttributesBag())->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
            new Reward("Cosmetic Bag", (new CosmeticBag())->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new CosmeticBag())->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 30),
            new Reward("Random Ability Scroll", (new AbilityScroll(random_int(0, 4)))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new AbilityScroll(random_int(0, 4)))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 30),
            new Reward("Boss Tag", VanillaItems::NAME_TAG()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_RED . "Boss" . TextFormat::RESET . TextFormat::GRAY . " Tag"), function (NexusPlayer $player): void {
                $tag = TextFormat::BOLD . TextFormat::DARK_RED . "Boss";
                if ($player->getDataSession()->hasTag($tag)) {
                    $player->sendMessage(TextFormat::RED . "Unlucky! You got a duplicate tag!");
                    return;
                }
                $player->getDataSession()->addTag($tag);
            }, 100),
            new Reward("Boss Helmet", VanillaItems::DIAMOND_HELMET()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Helmet"), function (NexusPlayer $player): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_HELMET(), TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Helmet", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 8),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::DIVINE_PROTECTION), 1),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::NOURISH), 7),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::EVADE), 5),
                ], [
                    "hasBossAttribute" => new StringTag("true")
                ]))->getItemForm();
                if ($player->getInventory()->canAddItem($item)) {
                    $player->getInventory()->addItem(ItemHandler::setLoreForItem($item));
                } else {
                    $player->getDataSession()->addToInbox(ItemHandler::setLoreForItem($item));
                }
            }, 50),
            new Reward("Boss Chestplate", VanillaItems::DIAMOND_CHESTPLATE()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Chestplate"), function (NexusPlayer $player): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_CHESTPLATE(), TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Chestplate", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 8),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::DIVINE_PROTECTION), 1),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::NOURISH), 7),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::FORTIFY), 1),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::EVADE), 5),
                ], [
                    "hasBossAttribute" => new StringTag("true")
                ]))->getItemForm();
                if ($player->getInventory()->canAddItem($item)) {
                    $player->getInventory()->addItem(ItemHandler::setLoreForItem($item));
                } else {
                    $player->getDataSession()->addToInbox(ItemHandler::setLoreForItem($item));
                }
            }, 50),
            new Reward("Boss Leggings", VanillaItems::DIAMOND_LEGGINGS()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Leggings"), function (NexusPlayer $player): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_LEGGINGS(), TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Leggings", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 8),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::DIVINE_PROTECTION), 1),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::NOURISH), 7),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::EVADE), 5),
                ], [
                    "hasBossAttribute" => new StringTag("true")
                ]))->getItemForm();
                if ($player->getInventory()->canAddItem($item)) {
                    $player->getInventory()->addItem(ItemHandler::setLoreForItem($item));
                } else {
                    $player->getDataSession()->addToInbox(ItemHandler::setLoreForItem($item));
                }
            }, 50),
            new Reward("Boss Boots", VanillaItems::DIAMOND_BOOTS()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Boots"), function (NexusPlayer $player): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_BOOTS(), TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Boots", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 8),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::DIVINE_PROTECTION), 1),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::NOURISH), 7),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::EVADE), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::QUICKENING), 1),
                ], [
                    "hasBossAttribute" => new StringTag("true")
                ]))->getItemForm();
                if ($player->getInventory()->canAddItem($item)) {
                    $player->getInventory()->addItem(ItemHandler::setLoreForItem($item));
                } else {
                    $player->getDataSession()->addToInbox(ItemHandler::setLoreForItem($item));
                }
            }, 50),
            new Reward("Boss Sword", VanillaItems::DIAMOND_SWORD()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Sword"), function (NexusPlayer $player): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_SWORD(), TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Boss Sword", [], [
                    new EnchantmentInstance((VanillaEnchantments::SHARPNESS()), 7),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::BLEED), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::ANNIHILATION), 5),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::WITHER), 3),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::GUILLOTINE), 10),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::BERSERK), 1)
                ]))->getItemForm();
                if ($player->getInventory()->canAddItem($item)) {
                    $player->getInventory()->addItem($item);
                } else {
                    $player->getDataSession()->addToInbox($item);
                }
            }, 50),
        ]);
    }

    /**
     * @param NexusPlayer $player
     *
     * @throws UtilsException
     */
    public function spawnTo(NexusPlayer $player): void
    {
        $particle = $player->getFloatingText($this->getName());
        if ($particle !== null) {
            return;
        }
        $player->addFloatingText(Position::fromObject($this->getPosition()->add(0.5, 1.25, 0.5), $this->getPosition()->getWorld()), $this->getName(), TextFormat::DARK_RED . TextFormat::BOLD .  "Boss Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::DARK_RED . $player->getDataSession()->getKeys($this) . TextFormat::WHITE . " keys");
    }

    /**
     * @param NexusPlayer $player
     *
     * @throws UtilsException
     */
    public function updateTo(NexusPlayer $player): void
    {
        $particle = $player->getFloatingText($this->getName());
        if ($particle === null) {
            $this->spawnTo($player);
        }
        $text = $player->getFloatingText($this->getName());
        $text->update(TextFormat::DARK_RED . TextFormat::BOLD .  "Boss Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::DARK_RED . $player->getDataSession()->getKeys($this) . TextFormat::WHITE . " keys");
        $text->sendChangesTo($player);
    }

    /**
     * @param NexusPlayer $player
     */
    public function despawnTo(NexusPlayer $player): void
    {
        $particle = $player->getFloatingText($this->getName());
        if ($particle !== null) {
            $particle->despawn($player);
        }
    }

    /**
     * @param Reward $reward
     * @param NexusPlayer $player
     *
     * @throws UtilsException
     */
    public function showReward(Reward $reward, NexusPlayer $player): void
    {
        $particle = $player->getFloatingText($this->getName());
        if ($particle === null) {
            $this->spawnTo($player);
        }
        $text = $player->getFloatingText($this->getName());
        $text->update(TextFormat::BOLD . TextFormat::DARK_RED . $reward->getName());
        $text->sendChangesTo($player);
    }

    /**
     * @param Reward $reward
     *
     * @return string
     */
    public function getRewardDisplayName(Reward $reward): string
    {
        return TextFormat::BOLD . TextFormat::DARK_RED . $reward->getName();
    }
}
