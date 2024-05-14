<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price\Event;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;

class ItemBuyEvent extends PlayerEvent {

    /** @var Item */
    private $item;

    /** @var int */
    private $spent;

    /**
     * ItemBuyEvent constructor.
     *
     * @param NexusPlayer $player
     * @param Item $item
     * @param int $spent
     */
    public function __construct(NexusPlayer $player, Item $item, int $spent) {
        $this->player = $player;
        $this->item = $item;
        $this->spent = $spent;
    }

    /**
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }

    /**
     * @return int
     */
    public function getSpent(): int {
        return $this->spent;
    }
}