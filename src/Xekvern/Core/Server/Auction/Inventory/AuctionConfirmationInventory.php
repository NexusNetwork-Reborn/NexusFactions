<?php

namespace Xekvern\Core\Server\Auction\Inventory;

use libs\muqsit\arithmexp\Util;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Auction\AuctionEntry;
use Xekvern\Core\Utils\Utils;

class AuctionConfirmationInventory extends InvMenu {

   /** @var AuctionEntry */
   private $entry;

    /**
     * AuctionConfirmationInventory constructor.
     */
    public function __construct(AuctionEntry $entry) {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_HOPPER));
        $this->entry = $entry;
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . "Confirm Purchase");
        $this->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $slot = $action->getSlot();
            $itemClicked = $transaction->getItemClicked();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($slot === 0 or $slot === 1) {
                if(!$this->entry->isExpired()) {
                    $this->entry->buy($player);
                }
                $player->removeCurrentWindow($action->getInventory());
            }
            if($slot === 3 or $slot === 4) {
                $player->removeCurrentWindow($action->getInventory());
            }
        }));
    }

    public function initItems(): void {
        $this->inventory->clearAll();

        $confirmationItem = clone $this->entry->getItem()->setCount($this->entry->getCount());
        $price = $this->entry->getBuyPrice();
        $seller = $this->entry->getSeller();
        $remainingTime = $this->entry->getRemainingTime();

        if($remainingTime > 0) {
            $lore = [];
            $lore[] = " ";
            $lore[] = TextFormat::RESET . TextFormat::WHITE . "Seller: " . TextFormat::YELLOW . $seller;
            $lore[] = TextFormat::RESET . TextFormat::WHITE . "Price: " . TextFormat::YELLOW . "$" . $price;
            $lore[] = TextFormat::RESET . TextFormat::WHITE . "Expires: " . TextFormat::RED . Utils::secondsToTime($remainingTime);
        } else {
            $lore = [];
            $lore[] = " ";
            $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "EXPIRED";
        }
        $confirmationItem->setLore(array_merge($confirmationItem->getLore(), $lore));
        $this->getInventory()->setItem(2, $confirmationItem);

        $confirm = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN())->asItem();
        $confirm->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "Confirm Purchase");
        $confirm->setLore([
            "",
            TextFormat::RESET . TextFormat::GRAY . "Click to confirm purchase.",
        ]);
        $this->getInventory()->setItem(0, $confirm);
        $this->getInventory()->setItem(1, $confirm);

        $cancel = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem();
        $cancel->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Cancel Purchase");
        $cancel->setLore([
            TextFormat::RESET . TextFormat::GRAY . "Click to cancel purchase.",
        ]);
        $this->getInventory()->setItem(3, $cancel);
        $this->getInventory()->setItem(4, $cancel);
    }
}