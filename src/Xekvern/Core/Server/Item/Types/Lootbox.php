<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Item\Task\LootboxTask;
use Xekvern\Core\Server\World\WorldHandler;
use Xekvern\Core\Server\World\Utils\GeneratorId;

class Lootbox extends ClickableItem {

    const LOOTBOX = "Lootbox";
    const LOOTBOX_TYPE = "LootboxType";
    const LOOTBOX_CUSTOM_NAME = "LootboxCustomName";

    /**
     * Lootbox constructor.
     */
    public function __construct(string $type, string $customName) {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "Lootbox: " . $customName;
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Receive rewards! Only best for success";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "Good Rewards";
        foreach(Lootbox::getRewards($type) as $reward) {
            if($reward instanceof Reward) {
                $item = $reward->getItem();
                if ($reward->getChance() >= 31) {
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . " * " . TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . $item->getCount() . "x" . TextFormat::RESET . " " . $item->getCustomName();
                }
            }
        }
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "Jackpot Rewards";
        foreach(Lootbox::getRewards($type) as $reward) {
            if($reward instanceof Reward) {
                $item = $reward->getItem();
                if ($reward->getChance() <= 30) { 
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . " * " . TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . $item->getCount() . "x" . TextFormat::RESET . " " . $item->getCustomName();
                }
            }
        }
        parent::__construct(VanillaBlocks::BEACON()->asItem(), $customName, $lore, [], [
            self::LOOTBOX => new StringTag(self::LOOTBOX),   
            self::LOOTBOX_TYPE => new StringTag($type),
            self::LOOTBOX_CUSTOM_NAME => new StringTag($customName)
        ]);    
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     *
     * @throws TranslatonException
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void { 
        if($player->getWorld()->getFolderName() !== $player->getCore()->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()) {
            $player->sendMessage(Translation::getMessage("onlyInSpawn"));
            return;
        }
        if(($player->getInventory()->getSize() - count($player->getInventory()->getContents())) < 6) {
            $player->sendMessage(Translation::getMessage("fullInventory"));
            return;
        }
        $type = $tag->getString(Lootbox::LOOTBOX_TYPE);
        $rewards = self::getRewards($type);
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
        $player->getCore()->getScheduler()->scheduleRepeatingTask(new LootboxTask($player, $tag->getString(Lootbox::LOOTBOX_CUSTOM_NAME), $rewards), 2);
    }

    /**
     * @param string $type
     */
    public static function getRewards(string $type): array {
        switch($type) {
            case "Husk":
                $rewards = [
                    new Reward("$850,000", (new MoneyNote(850000))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new MoneyNote(850000))->getItemForm());
                    }, 50),
                    new Reward("$2,500,000", (new MoneyNote(2500000))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new MoneyNote(2500000))->getItemForm());
                    }, 60),
                    new Reward("$12,500,000", (new MoneyNote(12500000))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new MoneyNote(12500000))->getItemForm());
                    }, 60),
                    new Reward("Alien Boss Egg", (new BossEgg("Alien"))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new BossEgg("Alien"))->getItemForm());
                    }, 100),
                    new Reward("16x Sacred Stone", (new SacredStone())->getItemForm()->setCount(16), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new SacredStone())->getItemForm()->setCount(16));
                    }, 85),
                    new Reward("Deity Kit", (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Deity")))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Deity")))->getItemForm());
                    }, 100),
                    new Reward("Holy Box", VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::BOLD . TextFormat::YELLOW . "Random Holy Box"), function(NexusPlayer $player): void {
                        $kits = Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits();
                        $kit = $kits[array_rand($kits)];
                        $player->getInventory()->addItem((new HolyBox($kit))->getItemForm());
                    }, 15),
                    new Reward("8x Ultra Crate Key Note", (new CrateKeyNote(Crate::ULTRA, 8))->getItemForm(), function(NexusPlayer $player): void {
                        $item = (new CrateKeyNote(Crate::ULTRA, 8))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 70),
                    new Reward("3x Legendary Crate Key Note", (new CrateKeyNote(Crate::LEGENDARY, 3))->getItemForm(), function(NexusPlayer $player): void {
                        $item = (new CrateKeyNote(Crate::LEGENDARY, 3))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 30),
                    new Reward("1x Amethyst Generator", (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::AMETHYST, false)))->getItemForm(), function(NexusPlayer $player): void {    
                        $item = (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::AMETHYST, false)))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 30),
                    new Reward("Mystery Spawner Bag", (new MysterySpawnerBag())->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new MysterySpawnerBag())->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 50),
                    new Reward("Cosmetic Bag", (new CosmeticBag())->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new CosmeticBag())->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 50),
                    new Reward("Enchantment Scroll", (new EnchantmentScroll())->getItemForm()->setCount(8), function (NexusPlayer $player): void {
                        $item = (new EnchantmentScroll())->getItemForm()->setCount(8);
                        $player->getInventory()->addItem($item);
                    }, 25),
                    new Reward("Attributes Bag", (new AttributesBag())->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new AttributesBag())->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 30),
                    new Reward("King Rank Note", (new RankNote(Nexus::getInstance()->getPlayerManager()->getRankHandler()->getRankByIdentifier(Rank::KING)))->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new RankNote(Nexus::getInstance()->getPlayerManager()->getRankHandler()->getRankByIdentifier(Rank::KING)))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 10),
                ];
                return $rewards;
            case "SOTW":
                $rewards = [
                    new Reward("$500,000", (new MoneyNote(500000))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new MoneyNote(500000))->getItemForm());
                    }, 100),
                    new Reward("$1,500,000", (new MoneyNote(1500000))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new MoneyNote(1500000))->getItemForm());
                    }, 100),
                    new Reward("5x Sacred Stone", (new SacredStone())->getItemForm()->setCount(5), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new SacredStone())->getItemForm()->setCount(5));
                    }, 85),
                    new Reward("Spartan Kit", (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Spartan")))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Spartan")))->getItemForm());
                    }, 100),
                    new Reward("Prince Kit", (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Prince")))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Prince")))->getItemForm());
                    }, 100),
                    new Reward("3x Ultra Crate Key Note", (new CrateKeyNote(Crate::ULTRA, 3))->getItemForm(), function(NexusPlayer $player): void {
                        $item = (new CrateKeyNote(Crate::ULTRA, 3))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 70),
                    new Reward("1x Legendary Crate Key Note", (new CrateKeyNote(Crate::LEGENDARY, 1))->getItemForm(), function(NexusPlayer $player): void {
                        $item = (new CrateKeyNote(Crate::LEGENDARY, 1))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 70),
                    new Reward("Generators Bag", (new GeneratorsBag())->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new GeneratorsBag())->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 50),
                    new Reward("Cosmetic Bag", (new CosmeticBag())->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new CosmeticBag())->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 50),
                ];
                return $rewards;
            case "Royalty":
                $rewards = [
                    new Reward("$250,000", (new MoneyNote(500000))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new MoneyNote(250000))->getItemForm());
                    }, 100),
                    new Reward("$2,500,000", (new MoneyNote(2500000))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new MoneyNote(2500000))->getItemForm());
                    }, 100),
                    new Reward("5x Sacred Stone", (new SacredStone())->getItemForm()->setCount(5), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new SacredStone())->getItemForm()->setCount(5));
                    }, 85),
                    new Reward("Spartan Kit", (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Spartan")))->getItemForm(), function(NexusPlayer $player): void {
                        $player->getInventory()->addItem((new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Spartan")))->getItemForm());
                    }, 100),
                    new Reward("5x Ultra Crate Key Note", (new CrateKeyNote(Crate::ULTRA, 5))->getItemForm(), function(NexusPlayer $player): void {
                        $item = (new CrateKeyNote(Crate::ULTRA, 5))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 70),
                    new Reward("3x Legendary Crate Key Note", (new CrateKeyNote(Crate::LEGENDARY, 3))->getItemForm(), function(NexusPlayer $player): void {
                        $item = (new CrateKeyNote(Crate::LEGENDARY, 3))->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 70),
                    new Reward("4x Diamond Generator", (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::DIAMOND, false)))->getItemForm()->setCount(4), function(NexusPlayer $player): void {    
                        $item = (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::DIAMOND, false)))->getItemForm()->setCount(4);
                        $player->getInventory()->addItem($item);
                    }, 15),
                    new Reward("Mystery Spawner Bag", (new MysterySpawnerBag())->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new MysterySpawnerBag())->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 50),
                    new Reward("Generators Bag", (new GeneratorsBag())->getItemForm(), function (NexusPlayer $player): void {
                        $item = (new GeneratorsBag())->getItemForm();
                        $player->getInventory()->addItem($item);
                    }, 50),
                ];
                return $rewards;
            default:
                return [];
        }
    }
}