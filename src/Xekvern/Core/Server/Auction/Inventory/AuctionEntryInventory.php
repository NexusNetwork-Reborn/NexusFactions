<?php

namespace Xekvern\Core\Server\Auction\Inventory;

use Carbon\Translator;
use libs\muqsit\arithmexp\Util;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Auction\AuctionEntry;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Utils\Utils;

class AuctionEntryInventory extends InvMenu {

   /** @var Item */
   private $item;

   /** @var int */
   private $amount;

    /** @var string */
    private $boughtItem = "False"; 

    /**
     * AuctionEntryInventory constructor.
     */
    public function __construct(Item $item, int $amount) {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_HOPPER));
        $this->item = $item;
        $this->amount = $amount;
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . "Confirm List Item");
        $this->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $slot = $action->getSlot();
            $itemClicked = $transaction->getItemClicked();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($slot === 0 or $slot === 1) {
                $item = $this->item;
                $amount = $this->amount;
                $cost = (30/100) * $amount;
                if ($player->getDataSession()->getBalance() <= (int)$cost) {
                    $player->sendMessage(Translation::RED . "You do not have enough balance to pay the cost of adding this item to the auction list.");
                    $player->playErrorSound();
                    $player->removeCurrentWindow($action->getInventory());
                    return;
                }
                $handler = Nexus::getInstance()->getServerManager()->getAuctionHandler();
                $player->getDataSession()->subtractFromBalance((int)$cost);
                $handler->addAuction(new AuctionEntry($item, $item->getCount(), $player->getName(), $handler->getNewIdentifier(), time(), $amount));
                $name = $item->hasCustomName() ? $item->getCustomName() : $item->getName();
                $name .= TextFormat::RESET . TextFormat::GRAY . " * " . TextFormat::WHITE . $item->getCount();
                $player->sendMessage(Translation::getMessage("addAuctionEntry", [
                    "item" => $name,
                    "price" => TextFormat::LIGHT_PURPLE . "$" . number_format($amount)
                ]));
                $this->boughtItem = "True";
                $player->removeCurrentWindow($action->getInventory());
            }
            if($slot === 3 or $slot === 4) {
                $this->boughtItem = "False";
                $player->removeCurrentWindow($action->getInventory());
            }
        }));
        $this->setInventoryCloseListener(function(NexusPlayer $player, InvMenuInventory $inventory): void {
            if($this->boughtItem === "True") {
                return;
            }
            $player->getInventory()->addItem($this->item);
        });
    }

    public function initItems(): void {
        $this->inventory->clearAll();
        $confirmationItem = clone $this->item;
        $amount = $this->amount;
        $lore = [];
        $lore[] = " ";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "(30% Cost of Pricing)";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST " . TextFormat::RESET . TextFormat::WHITE . "$" . number_format((30/100) * $amount);
        $confirmationItem->setLore(array_merge($confirmationItem->getLore(), $lore));
        $this->getInventory()->setItem(2, $confirmationItem);

        $confirm = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN())->asItem();
        $confirm->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "Confirm Listing");
        $confirm->setLore([
            TextFormat::RESET . TextFormat::GRAY . "(30% Cost of Pricing)",
            TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST " . TextFormat::RESET . TextFormat::WHITE . "$" . number_format((30/100) * $amount),
            "",
            TextFormat::RESET . TextFormat::GRAY . "Click to confirm listing.",
        ]);
        $this->getInventory()->setItem(0, $confirm);
        $this->getInventory()->setItem(1, $confirm);

        $cancel = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem();
        $cancel->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Cancel Listing");
        $cancel->setLore([
            TextFormat::RESET . TextFormat::GRAY . "Click to cancel listing.",
        ]);
        $this->getInventory()->setItem(3, $cancel);
        $this->getInventory()->setItem(4, $cancel);
    }
}