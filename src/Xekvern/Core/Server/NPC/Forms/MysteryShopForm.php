<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\NPC\Forms;

use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentRemover;
use Xekvern\Core\Server\Item\Types\HolyBox;
use Xekvern\Core\Server\Item\Types\LuckyBlock;
use Xekvern\Core\Server\Item\Types\MonthlyCrate;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Types\Soul;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Translation\Translation;

class MysteryShopForm extends MenuForm
{

    /**
     * MysteryShopForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player)
    {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Voldemort's Brewery";
        $souls = 0;
        foreach ($player->getInventory()->getContents() as $item) {
            $tag = $item->getNamedTag()->getTag(CustomItem::CUSTOM);
            if ($tag instanceof CompoundTag and !$tag->getTag(Soul::SOUL) === null and $item->getTypeId() === ExtraVanillaItems::CLOCK()->getTypeId()) {
                $souls += $item->getCount();
            }
        }
        $text = "Souls: " . $souls;
        $options = [];
        $options[] = new MenuOption("Very Lucky Block (5 Souls)");
        $options[] = new MenuOption("Random Rare+ Enchantment (10 Souls)");
        if ($player->hasPermission("permission.subordinate")) {
            $options[] = new MenuOption("Sacred Stone (15 Souls)");
        } else {
            $options[] = new MenuOption(TextFormat::RED . "Unavailable for your level!");
        }
        if ($player->hasPermission("permission.knight")) {
            $options[] = new MenuOption("Iron Ore Mining Generator (30 Souls)");
            $options[] = new MenuOption("Holy Box (50 Souls)");
        } else {
            $options[] = new MenuOption(TextFormat::RED . "Unavailable for your level!");
            $options[] = new MenuOption(TextFormat::RED . "Unavailable for your level!");
        }
        if ($player->hasPermission("permission.hoplite")) {
            $options[] = new MenuOption("Enchantment Remover (25 Souls)");
            $options[] = new MenuOption("Random Godly Enchantment (70 Souls)");
        } else {
            $options[] = new MenuOption(TextFormat::RED . "Unavailable for your level!");
            $options[] = new MenuOption(TextFormat::RED . "Unavailable for your level!");
        }
        if ($player->hasPermission("permission.titan")) {
            $options[] = new MenuOption("Monthly Kit (100 Souls)");
            $options[] = new MenuOption("1000 Use Sell Wand (150 Souls)");
        } else {
            $options[] = new MenuOption(TextFormat::RED . "Unavailable for your level!");
            $options[] = new MenuOption(TextFormat::RED . "Unavailable for your level!");
        }
        parent::__construct($title, $text, $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, int $selectedOption): void
    {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $souls = null;
        foreach ($player->getInventory()->getContents() as $item) {
            $tag = $item->getNamedTag()->getTag(CustomItem::CUSTOM);
            if ($tag instanceof CompoundTag and !$tag->getTag(Soul::SOUL) === null and $item->getTypeId() === ExtraVanillaItems::CLOCK()->getTypeId()) {
                if ($souls === null) {
                    $souls = clone $item;
                    continue;
                }
                $souls->setCount($souls->getCount() + $item->getCount());
            }
        }
        if ($souls === null) {
            $player->sendMessage(Translation::getMessage("notEnoughSouls"));
            return;
        }
        $option = $this->getOption($selectedOption);
        if ($player->getInventory()->getSize() === count($player->getInventory()->getContents())) {
            $player->sendMessage(Translation::getMessage("fullInventory"));
            return;
        }
        switch ($option->getText()) {
            case "Very Lucky Block (5 Souls)":
                $points = 5;
                $item = (new LuckyBlock(100))->getItemForm();
                break;
            case "Random Rare+ Enchantment (10 Souls)":
                $points = 10;
                switch (mt_rand(1, 5)) {
                    case 1:
                    case 2:
                    case 3:
                        $enchantment = ItemHandler::getRandomEnchantment(Rarity::RARE);
                        break;
                    case 4:
                        $enchantment = ItemHandler::getRandomEnchantment(Rarity::MYTHIC);
                        break;
                    case 5:
                        $enchantment = ItemHandler::getRandomEnchantment(Enchantment::RARITY_GODLY);
                        break;
                }
                $item = (new EnchantmentBook($enchantment, 1, 100))->getItemForm();
                break;
            case "Sacred Stone (15 Souls)":
                $points = 15;
                $item = (new SacredStone())->getItemForm();
                break;
            case "Iron Ore Mining Generator (30 Souls)":
                $points = 30;
                $item = VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::MAGENTA());
                break;
            case "Holy Box (50 Souls)":
                $kits = Nexus::getInstance()->getServerManager()->getKitHandler()->getSacredKits();
                $kit = $kits[array_rand($kits)];
                $points = 50;
                $item = (new HolyBox($kit))->getItemForm();
                break;
            case "Enchantment Remover (25 Souls)":
                $points = 25;
                $item = (new EnchantmentRemover(100))->getItemForm();
                break;
            case "Random Godly Enchantment (70 Souls)":
                $points = 70;
                $item = (new EnchantmentBook(ItemHandler::getRandomEnchantment(Enchantment::RARITY_GODLY), mt_rand(1, 100)))->getItemForm();
                break;
            case "Reaper Kit (100 Souls)":
                $points = 100;
                $item = (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Reaper")))->getItemForm();
                break;
            case "1000 Use Sell Wand (150 Souls)":
                $points = 150;
                $item = (new SellWandNote(150))->getItemForm();
                break;
            case "Monthly Crate (700 Souls)":
                $points = 700;
                $item = (new MonthlyCrate())->getItemForm();
                break;
            default:
                return;
        }
        if ($souls->getCount() < $points) {
            $player->sendMessage(Translation::getMessage("notEnoughSouls"));
            return;
        }
        $player->getInventory()->remove($souls->setCount($points));
        $player->sendMessage(Translation::getMessage("buy", [
            "amount" => TextFormat::GREEN . "x1",
            "item" => TextFormat::DARK_GREEN . $item->getCustomName(),
            "price" => TextFormat::LIGHT_PURPLE . "$points Souls",
        ]));
        $player->getInventory()->addItem($item);
    }
}