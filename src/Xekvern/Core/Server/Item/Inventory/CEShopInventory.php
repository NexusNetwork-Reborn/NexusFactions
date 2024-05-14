<?php

namespace Xekvern\Core\Server\Item\Inventory;

use Xekvern\Core\Player\NexusPlayer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\Enchant;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentCrystal;
use Xekvern\Core\Translation\Translation;

class CEShopInventory extends InvMenu {

    /**
     * CEShopInventory constructor.
     */
    public function __construct() {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_CHEST));
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Enchantments Shop");
        $this->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $slot = $action->getSlot();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            $amount = 0;
            if($slot === 11) {
                $amount = 10;
                $enchantment = ItemHandler::getRandomEnchantment(Rarity::COMMON);  
                if(mt_rand(1, 2) === mt_rand(1, 2)) {
                    $item = (new EnchantmentCrystal($enchantment))->getItemForm();
                }
                else {
                    $item = (new EnchantmentBook($enchantment, mt_rand(0, 100)))->getItemForm();
                } 
                if($player->getXpManager()->getXpLevel() <= $amount) {
                    $player->removeCurrentWindow();
                    $player->playErrorSound();
                    $player->sendMessage(Translation::RED . "You do not have enough xp levels to buy this enchantment!");
                    return;
                }
                $player->getXpManager()->subtractXpLevels($amount);
                $player->sendMessage(TextFormat::BOLD . TextFormat::GREEN . "You have successfully bought an enchantment.");
                $player->playDingSound();
                if(!$player->getInventory()->canAddItem($item)) {
                    $player->getPosition()->getWorld()->dropItem($player->getPosition(), $item);
                    return;
                } else {
                    $player->getInventory()->addItem($item);
                }
            }
            if($slot === 12) {
                $amount = 25;
                $enchantment = ItemHandler::getRandomEnchantment(Rarity::UNCOMMON);
                if(mt_rand(1, 2) === mt_rand(1, 2)) {
                    $item = (new EnchantmentCrystal($enchantment))->getItemForm();
                }
                else {
                    $item = (new EnchantmentBook($enchantment, mt_rand(0, 100)))->getItemForm();
                }
                if($player->getXpManager()->getXpLevel() <= $amount) {
                    $player->removeCurrentWindow();
                    $player->playErrorSound();
                    $player->sendMessage(Translation::RED . "You do not have enough xp levels to buy this enchantment!");
                    return;
                }
                $player->getXpManager()->subtractXpLevels($amount);
                $player->sendMessage(TextFormat::BOLD . TextFormat::GREEN . "You have successfully bought an enchantment.");
                $player->playDingSound();
                if(!$player->getInventory()->canAddItem($item)) {
                    $player->getPosition()->getWorld()->dropItem($player->getPosition(), $item);
                    return;
                } else {
                    $player->getInventory()->addItem($item);
                }
            }
            if($slot === 13) {
                $amount = 40;
                $enchantment = ItemHandler::getRandomEnchantment(Rarity::RARE);
                if(mt_rand(1, 2) === mt_rand(1, 2)) {
                    $item = (new EnchantmentCrystal($enchantment))->getItemForm();
                }
                else {
                    $item = (new EnchantmentBook($enchantment, mt_rand(0, 100)))->getItemForm();
                }
                if($player->getXpManager()->getXpLevel() <= $amount) {
                    $player->removeCurrentWindow();
                    $player->playErrorSound();
                    $player->sendMessage(Translation::RED . "You do not have enough xp levels to buy this enchantment!");
                    return;
                }
                $player->getXpManager()->subtractXpLevels($amount);
                $player->sendMessage(TextFormat::BOLD . TextFormat::GREEN . "You have successfully bought an enchantment.");
                $player->playDingSound();
                if(!$player->getInventory()->canAddItem($item)) {
                    $player->getPosition()->getWorld()->dropItem($player->getPosition(), $item);
                    return;
                } else {
                    $player->getInventory()->addItem($item);
                }
            }
            if($slot === 14) {
                $amount = 60;
                $enchantment = ItemHandler::getRandomEnchantment(Rarity::MYTHIC);
                if(mt_rand(1, 2) === mt_rand(1, 2)) {
                    $item = (new EnchantmentCrystal($enchantment))->getItemForm();
                }
                else {
                    $item = (new EnchantmentBook($enchantment, mt_rand(0, 100)))->getItemForm();
                }
                if($player->getXpManager()->getXpLevel() <= $amount) {
                    $player->removeCurrentWindow();
                    $player->playErrorSound();
                    $player->sendMessage(Translation::RED . "You do not have enough xp levels to buy this enchantment!");
                    return;
                }
                $player->getXpManager()->subtractXpLevels($amount);
                $player->sendMessage(TextFormat::BOLD . TextFormat::GREEN . "You have successfully bought an enchantment.");
                $player->playDingSound();
                if(!$player->getInventory()->canAddItem($item)) {
                    $player->getPosition()->getWorld()->dropItem($player->getPosition(), $item);
                    return;
                } else {
                    $player->getInventory()->addItem($item);
                }
            }
            if($slot === 15) {
                $amount = 85;
                $enchantment = ItemHandler::getRandomEnchantment(Enchantment::RARITY_GODLY);
                if(mt_rand(1, 2) === mt_rand(1, 2)) {
                    $item = (new EnchantmentCrystal($enchantment))->getItemForm();
                }
                else {
                    $item = (new EnchantmentBook($enchantment, mt_rand(0, 100)))->getItemForm();
                }
                if($player->getXpManager()->getXpLevel() <= $amount) {
                    $player->removeCurrentWindow();
                    $player->playErrorSound();
                    $player->sendMessage(Translation::RED . "You do not have enough xp levels to buy this enchantment!");
                    return;
                }
                $player->getXpManager()->subtractXpLevels($amount);
                $player->sendMessage(TextFormat::BOLD . TextFormat::GREEN . "You have successfully bought an enchantment.");
                $player->playDingSound();
                if(!$player->getInventory()->canAddItem($item)) {
                    $player->getPosition()->getWorld()->dropItem($player->getPosition(), $item);
                    return;
                } else {
                    $player->getInventory()->addItem($item);
                }
            }
        }));
    }

    public function initItems(): void {
        $glass = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::WHITE())->asItem();
        for($i = 0; $i < 27; $i++) {
            if($i === 11) {
                $common = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::LIGHT_BLUE())->asItem();
                $common->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::BLUE . "Common Enchantment");
                $common->setLore([
                    "",
                    TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST" . TextFormat::RESET . " 10 XP LVL",
                ]);
                $this->inventory->setItem($i, $common);
                continue;
            }
            if($i === 12) {
                $uncommon = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLUE())->asItem();
                $uncommon->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_BLUE . "Uncommon Enchantment");
                $uncommon->setLore([
                    "",
                    TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST" . TextFormat::RESET . " 25 XP LVL",
                ]);
                $this->inventory->setItem($i, $uncommon);
                continue;
            }
            if($i === 13) {
                $rare = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::PINK())->asItem();
                $rare->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Rare Enchantment");
                $rare->setLore([
                    "",
                    TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST" . TextFormat::RESET . " 40 XP LVL",
                ]);
                $this->inventory->setItem($i, $rare);
                continue;
            }
            if($i === 14) {

                $mythic = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::CYAN())->asItem();
                $mythic->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "Mythic Enchantment");
                $mythic->setLore([
                    "",
                    TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST" . TextFormat::RESET . " 60 XP LVL",
                ]);
                $this->inventory->setItem($i, $mythic);
                continue;
            }
            if($i === 15) {
                $godly = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem();
                $godly->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Godly Enchantment");
                $godly->setLore([
                    "",
                    TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST" . TextFormat::RESET . " 85 XP LVL",
                ]);
                $this->inventory->setItem($i, $godly);
                continue;
            }
        }
    }
}