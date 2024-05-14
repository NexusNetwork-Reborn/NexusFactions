<?php

namespace Xekvern\Core\Server\Auction\Inventory;

use libs\muqsit\arithmexp\Util;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Auction\AuctionEntry;
use Xekvern\Core\Utils\Utils;

class AuctionOffersInventory extends InvMenu {

    /** @var AuctionEntry[] */
    private $entries;

    /** @var NexusPlayer */
    private $owner;

    /** @var int */
    private $page;

    /**
     * AuctionOffersInventory constructor.
     */
    public function __construct(NexusPlayer $owner) {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_DOUBLE_CHEST));
        $this->owner = $owner;
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . "Your Auction Listings");
        $this->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $slot = $action->getSlot();
            $itemClicked = $transaction->getItemClicked();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($slot === 53) {
                $player->removeCurrentWindow($action->getInventory());
                Nexus::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player): void {
                    (new AuctionPageInventory())->send($player);
                }), 20);
            }
            if($slot >= 0 and $slot <= 44) {
                if(isset($this->entries[$slot])) {
                    $entry = $this->entries[$slot];
                    $entry->cancel($player);
                    unset($this->entries[$slot]);
                    $this->getInventory()->setItem($slot, VanillaItems::AIR());
                }
            }
        }));
    }

    public function initItems(): void {
        $this->inventory->clearAll();
        $auctions = Nexus::getInstance()->getServerManager()->getAuctionHandler()->getAuctionsOf($this->owner);
        for($i = 0; $i < 54; $i++) {
            if($i >= 0 and $i <= 44 and (!empty($auctions))) {
                $auction = array_shift($auctions);
                $this->entries[$i] = $auction;
                $item = clone $auction->getItem();
                $lore = [];
                $lore[] = " ";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "--------------------";
                $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Click to cancel this offer!";
                $lore[] = " ";
                $lore[] = TextFormat::RESET . TextFormat::WHITE . "Expires: " . TextFormat::YELLOW . Utils::secondsToTime($auction->getRemainingTime());
                $lore[] = TextFormat::RESET . TextFormat::WHITE . "Price: " . TextFormat::YELLOW . "$" . $auction->getBuyPrice();
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "--------------------";
                $item->setLore(array_merge($item->getLore(), $lore));
                $this->getInventory()->setItem($i, $item);
            }
        }
        $info = VanillaItems::BOOK();
        $info->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Listing Info");
        $info->setLore([
            TextFormat::RESET . TextFormat::GRAY . "These are your current listings, all of",
            TextFormat::RESET . TextFormat::GRAY . "the items you have currently listed on",
            TextFormat::RESET . TextFormat::GRAY . "the auction house are displayed here.",
            " ",
            TextFormat::RESET . TextFormat::GRAY . "You can cancel and collection your",
            TextFormat::RESET . TextFormat::GRAY . "expired listings here.",
        ]);
        $this->getInventory()->setItem(45, $info);

        $home = VanillaBlocks::OAK_DOOR()->asItem();
        $home->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Return to Auction House");
        $home->setLore([
            TextFormat::RESET . TextFormat::GRAY . "Click here to return to the",
            TextFormat::RESET . TextFormat::GRAY . "auction house menu.",
        ]);
        $this->getInventory()->setItem(53, $home);
    }
}