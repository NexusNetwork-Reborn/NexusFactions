<?php

namespace Xekvern\Core\Server\Auction\Inventory;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Auction\AuctionEntry;

class AuctionPageInventory extends InvMenu {

    /** @var int */
    private $page;

    /** @var AuctionEntry[] */
    private $entryIndexes = [];

    /**
     * AuctionPageInventory constructor.
     */
    public function __construct(int $page = 1) {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_DOUBLE_CHEST));
        $this->page = $page;
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Auction House");
        $this->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $slot = $action->getSlot();
            $itemClicked = $transaction->getItemClicked();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($slot === 48) {
                $this->page = $this->page - 1;
                $this->initItems();
            }
            if($slot === 49) {
                $this->initItems();
            }
            if($slot === 50) {
                $this->page = $this->page + 1;
                $this->initItems();
            }
            if($slot === 53) {
                $player->removeCurrentWindow();
                $player->sendDelayedWindow(new AuctionOffersInventory($player));
            }
            if($slot >= 0 and $slot <= 44) {
                if(isset($this->entryIndexes[$slot])) {
                    $entry = $this->entryIndexes[$slot];
                    if(!$entry->isExpired()) {
                        $player->removeCurrentWindow();
                        $player->sendDelayedWindow(new AuctionConfirmationInventory($entry));
                    }
                }
            }
        }));
    }

    public function initItems(): void {
        $this->inventory->clearAll();
        $itemsPerPage = 44;
        $startIndex = ($this->page - 1) * $itemsPerPage;
        $endIndex = $startIndex + $itemsPerPage;
        $auctions = Nexus::getInstance()->getServerManager()->getAuctionHandler()->getAuctions();
        $auctionsToDisplay = array_slice($auctions, $startIndex, $itemsPerPage);
        $i = 0;
        foreach ($auctionsToDisplay as $auction) {
            if($auction->isExpired()) {
                continue;
            }
            $item = clone $auction->getItem();
            $lore = [];
            $lore[] = " ";
            $lore[] = TextFormat::RESET . TextFormat::GRAY . "--------------------";
            $lore[] = TextFormat::RESET . TextFormat::WHITE . "Seller: " . TextFormat::YELLOW . $auction->getSeller();
            $lore[] = TextFormat::RESET . TextFormat::WHITE . "Price: " . TextFormat::YELLOW . "$" . $auction->getBuyPrice();
            $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Click to buy this item!";
            $lore[] = TextFormat::RESET . TextFormat::GRAY . "--------------------";
            $item->setLore(array_merge($item->getLore(), $lore));
            $this->entryIndexes[$i] = $auction;
            $this->getInventory()->setItem($i, $item);
            $i++;
        }
        $item = VanillaItems::DIAMOND();
        $item->setCount($this->page);
        $item->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Actual Page");
        $item->setLore([
            TextFormat::RESET . TextFormat::GRAY . "View your current page of auctions."
        ]);
        $this->getInventory()->setItem(45, $item);

        $item = VanillaItems::PAPER();
        $item->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "<- Previous Page");
        $item->setLore([
            TextFormat::RESET . TextFormat::GRAY . "View the previous page of auctions.",
        ]);
        if($this->page > 1) {
            $this->getInventory()->setItem(48, $item);
        }

        $item = VanillaItems::ENDER_PEARL();
        $item->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "Refresh Page");
        $item->setLore([
            TextFormat::RESET . TextFormat::GRAY . "Click to refresh the available",
            TextFormat::RESET . TextFormat::GRAY . "auction listings."
        ]);
        $this->getInventory()->setItem(49, $item);

        $item = VanillaItems::PAPER();
        $item->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Next Page ->");
        $item->setLore([
            TextFormat::RESET . TextFormat::GRAY . "View the next page of auctions.",
        ]);
        if (count($auctions) > $endIndex) {
            $this->getInventory()->setItem(50, $item);
        }

        $item = VanillaItems::GOLD_INGOT();
        $item->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "How to Sell");
        $item->setLore([
            TextFormat::RESET . TextFormat::GRAY . "To list an item in the auctions, just hold",
            TextFormat::RESET . TextFormat::GRAY . "an item in your hand and type " . TextFormat::YELLOW . "/ah sell <price>",
        ]);
        $this->getInventory()->setItem(52, $item);

        $item = VanillaBlocks::ENDER_CHEST()->asItem();
        $item->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Your Auctions");
        $item->setLore([
            TextFormat::RESET . TextFormat::GRAY . "Click here to view all of the items",
            TextFormat::RESET . TextFormat::GRAY . "you are selling on the auction.",
        ]);
        $this->getInventory()->setItem(53, $item);
    }
}