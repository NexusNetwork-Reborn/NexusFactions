<?php

namespace Xekvern\Core\Server\Fund\Inventory;

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
use Xekvern\Core\Server\Fund\Forms\FundAmountForm;
use Xekvern\Core\Utils\Utils;

class FundInventory extends InvMenu {

    /**
     * FundInventory constructor.
     */
    public function __construct() {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_CHEST));
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Fund Goals");
        $this->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $slot = $action->getSlot();
            $mergedFunds = Nexus::getInstance()->getServerManager()->getFundHandler()->getAllMergeFunds();
            $fund = $mergedFunds[$slot] ?? null;
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if ($fund === null) {
                return;
            }
            $player->removeCurrentWindow();
            $player->sendForm(new FundAmountForm($player, $fund));
        }));
    }

    public function initItems(): void {
        $this->inventory->clearAll();
        $mergedFunds = Nexus::getInstance()->getServerManager()->getFundHandler()->getAllMergeFunds();
        $complete = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN())->asItem();
        $incomplete = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem();
        foreach ($mergedFunds as $fund) {
            $item = $fund->isFunded() ? clone $complete : clone $incomplete;
            $this->getInventory()->addItem($fund->createItem($item));
        }
    }
}