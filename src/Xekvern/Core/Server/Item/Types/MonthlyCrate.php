<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Item\Task\MonthlyCrateAnimationTask;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Server\World\Utils\GeneratorId;
use Xekvern\Core\Server\World\WorldHandler;
use Xekvern\Core\Translation\Translation;

class MonthlyCrate extends ClickableItem {

    const MONTH = "Month";

    /**
     * MonthlyCrate constructor.
     */
    public function __construct() {
        $month = date("F", time());
        $customName = TextFormat::RESET . TextFormat::OBFUSCATED . TextFormat::BOLD . TextFormat::RED . "|" . TextFormat::GOLD . "|" . TextFormat::YELLOW . "|" . TextFormat::GREEN . "|" . TextFormat::AQUA . "|" . TextFormat::LIGHT_PURPLE . "|" . TextFormat::RESET . TextFormat::WHITE . TextFormat::BOLD . " $month Crate";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Place in spawn to open this magical box!";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Receive 4 random possible items 1 bonus item!";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "Possible Rewards";
        foreach(MonthlyCrate::getRewards() as $reward) {
            if($reward instanceof Reward) {
                $item = $reward->getItem();
                $lore[] = TextFormat::RESET . TextFormat::GOLD . TextFormat::WHITE . " * " . TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . $item->getCount() . "x" . TextFormat::RESET . " " . $item->getCustomName();
            }
        }
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::DARK_AQUA . TextFormat::BOLD . "Bonus Item";
        $lore[] = TextFormat::RESET . TextFormat::DARK_AQUA . TextFormat::BOLD . " * " . TextFormat::RESET . TextFormat::AQUA . "1x Random Holy Box";
        parent::__construct(VanillaBlocks::ENDER_CHEST()->asItem(), $customName, $lore, [], [
            self::MONTH => new StringTag($month),
            "UniqueId" => new StringTag(uniqid())
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
        if(($player->getInventory()->getSize() - count($player->getInventory()->getContents())) < 5) {
            $player->sendMessage(Translation::getMessage("fullInventory"));
            return;
        }
        $rewards = self::getRewards();
        /** @var PlayerInventory $inventory */
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
        $player->getCore()->getScheduler()->scheduleRepeatingTask(new MonthlyCrateAnimationTask($player, $tag->getString(MonthlyCrate::MONTH), $rewards), 1);
    }

    public static function getRewards(): array {
        $worldHandler = new WorldHandler(Nexus::getInstance());
        $rewards = [
            new Reward("$12,500,000", (new MoneyNote(12500000))->getItemForm(), function(NexusPlayer $player): void {
                $player->getInventory()->addItem((new MoneyNote(12500000))->getItemForm());
            }, 60),
            new Reward("16x Sacred Stone", (new SacredStone())->getItemForm()->setCount(16), function(NexusPlayer $player): void {
                $player->getInventory()->addItem((new SacredStone())->getItemForm()->setCount(16));
            }, 85),
            new Reward("Custom Tag", (new CustomTag())->getItemForm(), function(NexusPlayer $player): void {
                $player->getInventory()->addItem((new CustomTag())->getItemForm());
            }, 75),
            new Reward("Alien Boss Egg", (new BossEgg("Alien"))->getItemForm(), function(NexusPlayer $player): void {
                $player->getInventory()->addItem((new BossEgg("Alien"))->getItemForm());
            }, 100),
            new Reward("5x Legendary Crate Keys", (new CrateKeyNote(Crate::LEGENDARY, 5))->getItemForm(), function(NexusPlayer $player): void {
                $item = (new CrateKeyNote(Crate::LEGENDARY, 5))->getItemForm();
                $player->getInventory()->addItem($item);
            }, 100),
            new Reward("10x Epic Crate Keys", (new CrateKeyNote(Crate::EPIC, 10))->getItemForm(), function(NexusPlayer $player): void {
                $item = (new CrateKeyNote(Crate::EPIC, 10))->getItemForm();
                $player->getInventory()->addItem($item);
            }, 100),
            new Reward("3x Amethyst Generator", (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::AMETHYST, false)))->getItemForm()->setCount(3), function(NexusPlayer $player): void {    
                $item = (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::AMETHYST, false)))->getItemForm()->setCount(3);
                $player->getInventory()->addItem($item);
            }, 30),
            new Reward("Cosmetic Bag", (new CosmeticBag())->getItemForm(), function (NexusPlayer $player): void {
                $item = (new CosmeticBag())->getItemForm();
                $player->getInventory()->addItem($item);
            }, 100),
            new Reward("Attributes Bag", (new AttributesBag())->getItemForm(), function (NexusPlayer $player): void {
                $item = (new AttributesBag())->getItemForm();
                $player->getInventory()->addItem($item);
            }, 100),
            new Reward("Mystery Spawner Bag", (new MysterySpawnerBag())->getItemForm(), function (NexusPlayer $player): void {
                $item = (new MysterySpawnerBag())->getItemForm();
                $player->getInventory()->addItem($item);
            }, 75),
            new Reward("Generator Bag", (new GeneratorsBag())->getItemForm(), function (NexusPlayer $player): void {
                $item = (new GeneratorsBag())->getItemForm();
                $player->getInventory()->addItem($item);
            }, 75),
            new Reward("Deity Rank Note", (new RankNote(Nexus::getInstance()->getPlayerManager()->getRankHandler()->getRankByIdentifier(Rank::DEITY)))->getItemForm(), function (NexusPlayer $player): void {
                $item = (new RankNote(Nexus::getInstance()->getPlayerManager()->getRankHandler()->getRankByIdentifier(Rank::DEITY)))->getItemForm();
                $player->getInventory()->addItem($item);
            }, 75),
            new Reward("King Rank Note", (new RankNote(Nexus::getInstance()->getPlayerManager()->getRankHandler()->getRankByIdentifier(Rank::KING)))->getItemForm(), function (NexusPlayer $player): void {
                $item = (new RankNote(Nexus::getInstance()->getPlayerManager()->getRankHandler()->getRankByIdentifier(Rank::KING)))->getItemForm();
                $player->getInventory()->addItem($item);
            }, 75),
            new Reward("3 Random Ability Scroll", (new AttributeShard(random_int(0,2)))->getItemForm()->setCount(3), function (NexusPlayer $player): void {
                $item = (new AbilityScroll(random_int(0,4)))->getItemForm()->setCount(3);
                $player->getInventory()->addItem($item);
            }, 30),
            new Reward("Enchantment Scroll", (new EnchantmentScroll())->getItemForm()->setCount(12), function (NexusPlayer $player): void {
                $item = (new EnchantmentScroll())->getItemForm()->setCount(12);
                $player->getInventory()->addItem($item);
            }, 25),
        ];
        return $rewards;
    }
}