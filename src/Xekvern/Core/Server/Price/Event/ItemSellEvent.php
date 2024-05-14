<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price\Event;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;

class ItemSellEvent extends PlayerEvent {

    /** @var Item */
    private $item;

    /** @var int */
    private $profit;

    /**
     * ItemSellEvent constructor.
     *
     * @param NexusPlayer $player
     * @param Item $item
     * @param int $profit
     */
    public function __construct(NexusPlayer $player, Item $item, int $profit) {
        $this->player = $player;
        $this->item = $item;
        $this->profit = $profit;
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
    public function getProfit(): int {
        return $this->profit;
    }
}