<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Boss\Event;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\player\PlayerEvent;

class DamageBossEvent extends PlayerEvent {

    /** @var float */
    private $damage;

    /**
     * DamageBossEvent constructor.
     *
     * @param NexusPlayer $player
     * @param float $damage
     */
    public function __construct(NexusPlayer $player, float $damage) {
        $this->player = $player;
        $this->damage = $damage;
    }

    /**
     * @return float
     */
    public function getDamage(): float {
        return $this->damage;
    }
}