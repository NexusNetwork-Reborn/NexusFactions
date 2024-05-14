<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Gamble\Event;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\player\PlayerEvent;

class CoinFlipLoseEvent extends PlayerEvent {

    /** @var int */
    private $amount;

    /**
     * CoinFlipLoseEvent constructor.
     *
     * @param NexusPlayer $player
     * @param int $amount
     */
    public function __construct(NexusPlayer $player, int $amount) {
        $this->player = $player;
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAmount(): int {
        return $this->amount;
    }
}