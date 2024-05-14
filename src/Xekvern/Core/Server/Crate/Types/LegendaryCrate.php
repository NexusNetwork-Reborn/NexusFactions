<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate\Types;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\VanillaItems;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\AbilityScroll;
use Xekvern\Core\Server\Item\Types\AttributeShard;
use Xekvern\Core\Server\Item\Types\BossEgg;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\CosmeticShard;
use Xekvern\Core\Server\Item\Types\CreeperEgg;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Types\KOTHStarter;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Types\OreGenerator;
use Xekvern\Core\Server\Item\Types\Recon;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Utils\GeneratorId;
use Xekvern\Core\Server\World\WorldHandler;

class LegendaryCrate extends Crate {

    /**
     * LegendaryCrate constructor.
     *
     * @param Position $position\
     */
    public function __construct(Position $position) {
        parent::__construct(self::LEGENDARY, $position, [
            new Reward("5,000 XP", VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "5,000 XP"), function(NexusPlayer $player): void {
                $player->addXp(5000);
            }, 100),
            new Reward("10,000 XP", VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "10,000 XP"), function(NexusPlayer $player): void {
                $player->addXp(10000);
            }, 75),
            new Reward("1,500 Level XP", VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "1,500 Level XP"), function (NexusPlayer $player): void {
                $player->getXpManager()->addXp(1500);
            }, 100),
            new Reward("$750,000", (new MoneyNote(750000))->getItemForm(), function(NexusPlayer $player): void {
                $items = [
                    (new MoneyNote(750000))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 75),
            new Reward("$1,750,000", (new MoneyNote(1750000))->getItemForm(), function(NexusPlayer $player): void {
                $items = [
                    (new MoneyNote(1750000))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 55),
            new Reward("$7,500,000", (new MoneyNote(7500000))->getItemForm(), function(NexusPlayer $player): void {
                $items = [
                    (new MoneyNote(7500000))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 35),
            new Reward("Bedrock", VanillaBlocks::BEDROCK()->asItem()->setCount(64), function(NexusPlayer $player): void {
                $items = [
                    VanillaBlocks::BEDROCK()->asItem()->setCount(64),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 100),
            new Reward("TNT", VanillaBlocks::TNT()->asItem()->setCount(64), function(NexusPlayer $player): void {
                $items = [
                    VanillaBlocks::TNT()->asItem()->setCount(64),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 100),
            new Reward("x50 Sell Wand Note", VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "x50 Sell Wand Note"), function(NexusPlayer $player): void {
                $items = [
                    (new SellWandNote(50))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 50),
            new Reward("Sacred Stone", (new SacredStone())->getItemForm(), function(NexusPlayer $player): void {
                $items = [
                    (new SacredStone())->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 50),
            new Reward("Creeper Eggs", ExtraVanillaItems::CREEPER_SPAWN_EGG(), function(NexusPlayer $player): void {
                $items = [
                    (new CreeperEgg())->getItemForm()->setCount(8),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 100),
            new Reward("Lapis Lazuli Generator", (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::LAPIS_LAZULI, false)))->getItemForm()->setCount(3), function(NexusPlayer $player): void {
                $items = [
                    (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::LAPIS_LAZULI, false)))->getItemForm()->setCount(3),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Diamond Generator", (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::DIAMOND, false)))->getItemForm()->setCount(1), function(NexusPlayer $player): void {
                $items = [
                    (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::DIAMOND, false)))->getItemForm()->setCount(1),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
            new Reward("Random Mythic Enchantment", VanillaItems::ENCHANTED_BOOK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchantment"), function (NexusPlayer $player): void {
                $items = [
                    (new EnchantmentBook(ItemHandler::getRandomEnchantment(Rarity::MYTHIC), mt_rand(1, 100)))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 25),
            new Reward("Random Rare Enchantment", VanillaItems::ENCHANTED_BOOK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchantment"), function (NexusPlayer $player): void {
                $items = [
                    (new EnchantmentBook(ItemHandler::getRandomEnchantment(Rarity::RARE), mt_rand(1, 100)))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 35),
            new Reward("Random Godly Enchantment", VanillaItems::ENCHANTED_BOOK()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchantment"), function (NexusPlayer $player): void {
                $items = [
                    (new EnchantmentBook(ItemHandler::getRandomEnchantment(Enchantment::RARITY_GODLY), mt_rand(1, 100)))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 5),
            new Reward("Enchantment Scroll", (new EnchantmentScroll())->getItemForm(), function(NexusPlayer $player): void {
                $items = [
                    (new EnchantmentScroll())->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Recon", (new Recon())->getItemForm(), function(NexusPlayer $player): void {
                $items = [
                    (new Recon())->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Alien Boss Egg", (new BossEgg("Alien"))->getItemForm(), function(NexusPlayer $player): void {
                $items = [
                    (new BossEgg("Alien"))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("King Kit", VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "King Kit"), function(NexusPlayer $player): void {
                $items = [
                    (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("King")))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Spartan Kit", VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Spartan Kit"), function(NexusPlayer $player): void {
                $items = [
                    (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Spartan")))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
            new Reward("Sprinter Attribute Shard", (new AttributeShard(1))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new AttributeShard(1))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
            new Reward("Defender Attribute Shard", (new AttributeShard(2))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new AttributeShard(2))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
            new Reward("Cosmetic Shard", (new CosmeticShard(random_int(0,1), random_int(14,18)))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new CosmeticShard(random_int(0,1), random_int(14,18)))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
            new Reward("Random Ability Scroll", (new AbilityScroll(random_int(0,4)))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new AbilityScroll(random_int(0,4)))->getItemForm(),
                ];
                foreach($items as $item) {
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    }
                    else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 30),
            new Reward("Legend Tag", VanillaItems::NAME_TAG()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "Legend" . TextFormat::RESET . TextFormat::GRAY . " Tag"), function(NexusPlayer $player): void {
                $tag = TextFormat::BOLD . TextFormat::AQUA . "Legend";
                if($player->getDataSession()->hasTag($tag)) {
                    $player->sendMessage(TextFormat::RED . "Unlucky! You got a duplicate tag!");
                    return;
                }
                $player->getDataSession()->addTag($tag);
            }, 100),
            new Reward("Random Tag", VanillaItems::NAME_TAG()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Random" . TextFormat::RESET . TextFormat::GRAY . " Tag"), function(NexusPlayer $player): void {
                $tags = [
                    TextFormat::BOLD . TextFormat::RED . "M" . TextFormat::GOLD . "E" . TextFormat::YELLOW . "M" . TextFormat::GREEN . "E" . TextFormat::DARK_BLUE . "D",
                    TextFormat::BOLD . TextFormat::YELLOW . "Is" . TextFormat::GOLD . "It" . TextFormat::RED . "Reset" . TextFormat::DARK_RED . "Yet",
                    TextFormat::BOLD . TextFormat::BLUE . "Season" . TextFormat::AQUA . Nexus::SEASON,
                    TextFormat::BOLD . TextFormat::YELLOW . "Lucky",
                    TextFormat::BOLD . TextFormat::DARK_GRAY . "BOT",
                    TextFormat::BOLD . TextFormat::YELLOW . "GG" . TextFormat::AQUA . "No" . TextFormat::GREEN . "Ree",
                    TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Xek" . TextFormat::AQUA . "Is" . TextFormat::DARK_PURPLE . "Hot",
                    TextFormat::BOLD . TextFormat::DARK_RED . "Daddy" . TextFormat::AQUA . "David" . TextFormat::RED . "<3",
                    TextFormat::BOLD . TextFormat::GOLD . "David" . TextFormat::DARK_GRAY . "Got" . TextFormat::DARK_AQUA . "Scammed",
                    TextFormat::BOLD . TextFormat::DARK_RED . "Hype" . TextFormat::RED . "Beast",
                    TextFormat::BOLD . TextFormat::DARK_PURPLE . "PvP" . TextFormat::LIGHT_PURPLE . "God",
                    TextFormat::BOLD . TextFormat::RED . "100" . TextFormat::GREEN . "CPS",
                    TextFormat::BOLD . TextFormat::YELLOW . "M" . TextFormat::RED . "V" . TextFormat::BLUE . "P",
                    TextFormat::BOLD . TextFormat::GOLD . "Popeye" . TextFormat::YELLOW . "Chicken" . TextFormat::RED . "Sandwich",
                    TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "W" . TextFormat::DARK_AQUA . "E" . TextFormat::DARK_PURPLE . "E" . TextFormat::AQUA . "B",
                    TextFormat::BOLD . TextFormat::GREEN . "YO" . TextFormat::DARK_GRAY . "DA",
                    TextFormat::BOLD . TextFormat::DARK_PURPLE . "BEE" . TextFormat::LIGHT_PURPLE . "RUS",
                    TextFormat::BOLD . TextFormat::GREEN . "9000" . TextFormat::BLUE . "IQ",
                    TextFormat::BOLD . TextFormat::AQUA . "Ok" . TextFormat::DARK_AQUA . "Boomer",
                    TextFormat::BOLD . TextFormat::GOLD . "Simp" . TextFormat::YELLOW . "Nation",
                    TextFormat::BOLD . TextFormat::GREEN . "Baby" . TextFormat::DARK_GREEN . "Yoda",
                    TextFormat::BOLD . TextFormat::AQUA . "VSCO",
                    TextFormat::BOLD . TextFormat::DARK_RED . "OG",
                    TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "BAE",
                    TextFormat::BOLD . TextFormat::RED . "No" . TextFormat::GRAY . "Skillz",
                    TextFormat::BOLD . TextFormat::ITALIC . TextFormat::YELLOW . "SMOOVE",
                    TextFormat::BOLD . TextFormat::AQUA . "Duck" . TextFormat::LIGHT_PURPLE . "Gang",
                    TextFormat::BOLD . TextFormat::DARK_AQUA . "Plat" . TextFormat::GOLD . "Army",
                    TextFormat::BOLD . TextFormat::BLACK . "BLM",
                    TextFormat::BOLD . TextFormat::RED . "DONT" . TextFormat::DARK_RED . "LEAVE" . TextFormat::RED . "ME",
                ];
                $tag = $tags[array_rand($tags)];
                if($player->getDataSession()->hasTag($tag)) {
                    $player->sendMessage(TextFormat::RED . "Unlucky! You got a duplicate tag!");
                    return;
                }
                $player->getDataSession()->addTag($tag);
            }, 100),
        ]);
    }

    /**
     * @param NexusPlayer $player
     *
     * @throws UtilsException
     */
    public function spawnTo(NexusPlayer $player): void {
        $particle = $player->getFloatingText($this->getName());
        if($particle !== null) {
            return;
        }
        $player->addFloatingText(Position::fromObject($this->getPosition()->add(0.5, 1.25, 0.5), $this->getPosition()->getWorld()), $this->getName(), TextFormat::AQUA . TextFormat::BOLD .  "Legendary Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::AQUA . $player->getDataSession()->getKeys($this) . TextFormat::WHITE . " keys");
    }

    /**
     * @param NexusPlayer $player
     *
     * @throws UtilsException
     */
    public function updateTo(NexusPlayer $player): void {
        $particle = $player->getFloatingText($this->getName());
        if($particle === null) {
            $this->spawnTo($player);
        }
        $text = $player->getFloatingText($this->getName());
        $text->update(TextFormat::AQUA . TextFormat::BOLD .  "Legendary Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::AQUA . $player->getDataSession()->getKeys($this) . TextFormat::WHITE . " keys");
        $text->sendChangesTo($player);
    }

    /**
     * @param NexusPlayer $player
     */
    public function despawnTo(NexusPlayer $player): void {
        $particle = $player->getFloatingText($this->getName());
        if($particle !== null) {
            $particle->despawn($player);
        }
    }

    /**
     * @param Reward $reward
     * @param NexusPlayer $player
     *
     * @throws UtilsException
     */
    public function showReward(Reward $reward, NexusPlayer $player): void {
        $particle = $player->getFloatingText($this->getName());
        if($particle === null) {
            $this->spawnTo($player);
        }
        $text = $player->getFloatingText($this->getName());
        $text->update(TextFormat::BOLD . TextFormat::AQUA . $reward->getName());
        $text->sendChangesTo($player);
    }

    /**
     * @param Reward $reward
     *
     * @return string
     */
    public function getRewardDisplayName(Reward $reward): string {
        return TextFormat::BOLD . TextFormat::AQUA . $reward->getName();
    }
}
