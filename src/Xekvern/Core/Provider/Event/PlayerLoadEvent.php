<?php

declare(strict_types=1);

namespace Xekvern\Core\Provider\Event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlayerLoadEvent extends PlayerEvent {

    /**
     * PlayerLoadEvent constructor.
     *
     * @param Player $player
     */
    public function __construct(Player $player) {
        $this->player = $player;
    }
}