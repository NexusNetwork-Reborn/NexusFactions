<?php

namespace Xekvern\Core\Server\NPC\Inventory;

use Xekvern\Core\Player\NexusPlayer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Command\Forms\ItemInfoForm;
use Xekvern\Core\Server\Crate\Reward;
use Xekvern\Core\Server\Item\Types\Lootbox;

class ViewLootboxInventory extends InvMenu {

    /**
     * ViewLootboxInventory constructor.
     */
    public function __construct(Item $item) {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_CHEST));
        $this->initItems();
        $this->setName(TextFormat::RESET . $item->getCustomName());
        $this->setListener(self::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $slot = $action->getSlot();
            $player = $transaction->getPlayer();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            $item = $action->getSourceItem();
            $player->removeCurrentWindow();
            $player->sendDelayedForm(new ItemInfoForm($item, $this));
            return;
        }));
    }

    public function initItems(): void {
        foreach(Lootbox::getRewards("Husk") as $item) {
            if($item instanceof Reward) {
                $reward = $item->getItem();
                $this->getInventory()->addItem($reward);
            }
        }
    }
}