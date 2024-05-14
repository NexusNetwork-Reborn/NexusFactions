<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\Block;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\TNTLauncher;

class KOTHLootbag extends ClickableItem {

    const KOTH_LOOOTBAG = "KOTHLootbag";

    /**
     * KOTHLootbag constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "KOTH Lootbag";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Tap anywhere to open this mysterious bag.";
        parent::__construct(VanillaItems::GOLD_NUGGET(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::KOTH_LOOOTBAG => new StringTag(self::KOTH_LOOOTBAG)
        ]);    
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        $rewards = [
            (new CrateKeyNote(Crate::ULTRA, 4))->getItemForm(),
            (new SacredStone())->getItemForm()->setCount(mt_rand(3, 7)),
            (new CustomItem(VanillaItems::DIAMOND_SWORD(), TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "KOTH " . TextFormat::RESET . TextFormat::DARK_AQUA . "Sword", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 13),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::BLEED), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::ANNIHILATION), 6),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::GUILLOTINE), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHATTER), 2),
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_HELMET(), TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "KOTH " . TextFormat::RESET . TextFormat::DARK_AQUA . "Helmet", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 13),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::BLESS), 1),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 6),
            ],[
                "hasKothAttribute" => new StringTag("true")
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_CHESTPLATE(), TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "KOTH " . TextFormat::RESET . TextFormat::DARK_AQUA . "Chestplate", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 13),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::BLESS), 1),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 6),
            ],[
                "hasKothAttribute" => new StringTag("true")
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_LEGGINGS(), TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "KOTH " . TextFormat::RESET . TextFormat::DARK_AQUA . "Leggings", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 13),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::BLESS), 1),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 6),
            ],[
                "hasKothAttribute" => new StringTag("true")
            ]))->getItemForm(),
            (new CustomItem(VanillaItems::DIAMOND_BOOTS(), TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "KOTH " . TextFormat::RESET . TextFormat::DARK_AQUA . "Boots", [], [
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 13),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::IMMUNITY), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::NOURISH), 7),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::BLESS), 1),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EVADE), 6),
                new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::QUICKENING), 2),
            ],[
                "hasKothAttribute" => new StringTag("true")
            ]))->getItemForm(),
            (new SellWandNote(100))->getItemForm(),
            (new HolyBox( Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits()[array_rand( Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits())]))->getItemForm(),
            (new MoneyNote(1000000))->getItemForm(),
            (new Soul())->getItemForm()->setCount(3),
            (new XPNote(2500))->getItemForm()->setCount(1),
            (new CreeperEgg())->getItemForm()->setCount(32),
            (new TNTLauncher(3, 100, "TNT", ["Short", "Mid", "Long"][array_rand(["Short", "Mid", "Long"])]))->getItemForm(),
        ];
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
        Server::getInstance()->broadcastMessage(TextFormat::WHITE . $player->getName() . TextFormat::GRAY . " has opened a " . TextFormat::AQUA . TextFormat::BOLD . "KOTH Lootbag" . TextFormat::RESET . TextFormat::GRAY . " and received:");
        for($i = 0; $i < 3; $i++) {
            $item = $rewards[array_rand($rewards)];
            $name = $item->getName();
            if($item->hasCustomName()) {
                $name = $item->getCustomName();
            }
            Server::getInstance()->broadcastMessage(TextFormat::GRAY . TextFormat::BOLD . " * " . TextFormat::RESET . $name);
            $inventory->addItem(ItemHandler::setLoreForItem($item));
        }
        $player->playXpLevelUpSound();
    }
}