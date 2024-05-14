<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Koth\Event;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\player\PlayerEvent;

class KOTHCaptureEvent extends PlayerEvent {

    /**
     * CrateOpenEvent constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $this->player = $player;
    }
}