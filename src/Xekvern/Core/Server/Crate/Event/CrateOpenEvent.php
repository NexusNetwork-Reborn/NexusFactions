<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate\Event;

use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\player\PlayerEvent;

class CrateOpenEvent extends PlayerEvent {

    /** @var Crate */
    private $crate;

    /** @var int */
    private $amount;

    /**
     * CrateOpenEvent constructor.
     *
     * @param NexusPlayer $player
     * @param Crate $crate
     * @param int $amount
     */
    public function __construct(NexusPlayer $player, Crate $crate, int $amount) {
        $this->player = $player;
        $this->crate = $crate;
        $this->amount = $amount;
    }

    /**
     * @return Crate
     */
    public function getCrate(): Crate {
        return $this->crate;
    }

    /**
     * @return int
     */
    public function getAmount(): int {
        return $this->amount;
    }
}