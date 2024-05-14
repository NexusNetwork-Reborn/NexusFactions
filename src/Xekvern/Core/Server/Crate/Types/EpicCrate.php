<?php

declare(strict_types=1);

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
use Xekvern\Core\Server\Item\Types\BossEgg;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\CosmeticBag;
use Xekvern\Core\Server\Item\Types\CosmeticShard;
use Xekvern\Core\Server\Item\Types\CreeperEgg;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Types\GeneratorsBag;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Types\OreGenerator;
use Xekvern\Core\Server\Item\Types\Recon;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Types\TNTLauncher;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Utils\GeneratorId;
use Xekvern\Core\Server\World\WorldHandler;

class EpicCrate extends Crate
{

    /**
     * EpicCrate constructor.
     *
     * @param Position $position\
     */
    public function __construct(Position $position)
    {
        parent::__construct(self::EPIC, $position, [
            new Reward("1,000 XP", VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "1,000 XP"), function (NexusPlayer $player): void {
                $player->addXp(1000);
            }, 100),
            new Reward("300 Level XP", VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "300 Level XP"), function (NexusPlayer $player): void {
                $player->getXpManager()->addXp(300);
            }, 100),
            new Reward("$250,000", (new MoneyNote(250000))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new MoneyNote(250000))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 75),
            new Reward("Bedrock", VanillaBlocks::BEDROCK()->asItem()->setCount(64), function (NexusPlayer $player): void {
                $items = [
                    VanillaBlocks::BEDROCK()->asItem()->setCount(64),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 100),
            new Reward("x25 Sell Wand Note", (new SellWandNote(25))->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new SellWandNote(25))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 50),
            new Reward("Corrupted King Boss Egg", (new BossEgg("CorruptedKing"))->getItemForm(), function(NexusPlayer $player): void {
                $player->getInventory()->addItem((new BossEgg("CorruptedKing"))->getItemForm());
            }, 100),
            new Reward("Alien Boss Egg", (new BossEgg("Alien"))->getItemForm(), function(NexusPlayer $player): void {
                $player->getInventory()->addItem((new BossEgg("Alien"))->getItemForm());
            }, 100),
            new Reward("Sacred Stone", (new SacredStone())->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new SacredStone())->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 10),
            new Reward("Creeper Eggs", (new CreeperEgg())->getItemForm()->setCount(8), function (NexusPlayer $player): void {
                $items = [
                    (new CreeperEgg())->getItemForm()->setCount(8),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 100),
            new Reward("TNT Launcher", VanillaItems::STICK()->setCustomName(TextFormat::RESET . TextFormat::BLUE . "TNT Launcher"), function (NexusPlayer $player): void {
                $items = [
                    (new TNTLauncher(1, 50, "TNT", ["Short", "Mid", "Long"][array_rand(["Short", "Mid", "Long"])]))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 100),
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
            }, 20),
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
            }, 100),
            new Reward("Generators Bag", (new GeneratorsBag())->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new GeneratorsBag())->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 100),
            new Reward("Recon", (new Recon())->getItemForm(), function (NexusPlayer $player): void {
                $items = [
                    (new Recon())->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 80),
            new Reward("Prince Kit", VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Prince Kit"), function (NexusPlayer $player): void {
                $items = [
                    (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Prince")))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
            new Reward("Hoplite Kit", VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Hoplite Kit"), function (NexusPlayer $player): void {
                $items = [
                    (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Hoplite")))->getItemForm(),
                ];
                foreach ($items as $item) {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getDataSession()->addToInbox($item);
                    }
                }
            }, 20),
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
            new Reward("Epic Tag", VanillaItems::NAME_TAG()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "ImEpic" . TextFormat::RESET . TextFormat::GRAY . " Tag"), function (NexusPlayer $player): void {
                $tag = TextFormat::BOLD . TextFormat::DARK_PURPLE . "ImEpic";
                if ($player->getDataSession()->hasTag($tag)) {
                    $player->sendMessage(TextFormat::RED . "Unlucky! You got a duplicate tag!");
                    return;
                }
                $player->getDataSession()->addTag($tag);
            }, 100),
            new Reward("Random Tag", VanillaItems::NAME_TAG()->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Random" . TextFormat::RESET . TextFormat::GRAY . " Tag"), function (NexusPlayer $player): void {
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
                if ($player->getDataSession()->hasTag($tag)) {
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
    public function spawnTo(NexusPlayer $player): void
    {
        $particle = $player->getFloatingText($this->getName());
        if ($particle !== null) {
            return;
        }
        $player->addFloatingText(Position::fromObject($this->getPosition()->add(0.5, 1.25, 0.5), $this->getPosition()->getWorld()), $this->getName(), TextFormat::DARK_PURPLE . TextFormat::BOLD .  "Epic Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::DARK_PURPLE . $player->getDataSession()->getKeys($this) . TextFormat::WHITE . " keys");
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
        $text->update(TextFormat::DARK_PURPLE . TextFormat::BOLD .  "Epic Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::DARK_PURPLE . $player->getDataSession()->getKeys($this) . TextFormat::WHITE . " keys");
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
        $text->update(TextFormat::BOLD . TextFormat::DARK_PURPLE . $reward->getName());
        $text->sendChangesTo($player);
    }

    /**
     * @param Reward $reward
     *
     * @return string
     */
    public function getRewardDisplayName(Reward $reward): string
    {
        return TextFormat::BOLD . TextFormat::DARK_PURPLE . $reward->getName();
    }
}
