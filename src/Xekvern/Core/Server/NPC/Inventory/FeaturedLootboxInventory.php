<?php

namespace Xekvern\Core\Server\NPC\Inventory;

use Xekvern\Core\Player\NexusPlayer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\NPC\Forms\FeaturedLootboxForm;
use Xekvern\Core\Server\Item\Types\Lootbox;

class FeaturedLootboxInventory extends InvMenu {

    /**
     * FeaturedLootboxInventory constructor.
     */
    public function __construct() {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_HOPPER));
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Lootbox Merchant");
        $this->setListener(self::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $slot = $action->getSlot();
            $player = $transaction->getPlayer();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($slot === 2) {
                $lootbox = (new Lootbox("Husk", TextFormat::BOLD . TextFormat::DARK_GRAY. "Husky"))->getItemForm();
                $player->removeCurrentWindow();
                $player->sendDelayedForm(new FeaturedLootboxForm($lootbox));
            }
            return;
        }));
    }

    public function initItems(): void {
        $unknownelement = VanillaBlocks::ELEMENT_ZERO()->asItem();
        $unknownelement->setCustomName(" ");
        for($i = 0; $i < 5; $i++) {
            if($i === 2) {
                $lootbox = (new Lootbox("Husk", TextFormat::BOLD . TextFormat::DARK_GRAY. "Husky"))->getItemForm();
                $this->getInventory()->setItem($i, $lootbox);
                continue;
            }
            $this->getInventory()->setItem($i, $unknownelement);
        }
    }
}