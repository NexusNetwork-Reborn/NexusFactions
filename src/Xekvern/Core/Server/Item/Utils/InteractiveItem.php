<?php

namespace Xekvern\Core\Server\Item\Utils;

use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use Xekvern\Core\Player\NexusPlayer;

abstract class InteractiveItem extends CustomItem
{

    /**
     * @param NexusPlayer $player
     * @param Item $itemClickedWith
     * @param Item $itemClicked
     * @param SlotChangeAction $itemClickedWithAction
     * @param SlotChangeAction $itemClickedAction
     *
     * @return bool
     */
    public function onCombine(NexusPlayer $player, Item $itemClickedWith, Item $itemClicked, SlotChangeAction $itemClickedWithAction, SlotChangeAction $itemClickedAction): bool {
        return true;
    }
}