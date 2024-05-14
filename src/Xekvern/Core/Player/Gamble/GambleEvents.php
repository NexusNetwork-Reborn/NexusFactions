<?php

namespace Xekvern\Core\Player\Gamble;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Provider\Event\PlayerLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class GambleEvents implements Listener {

    /** @var Nexus */
    private $core;

    /**
     * GambleEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @priority NORMAL
     * @param PlayerLoadEvent $event
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $this->core->getPlayerManager()->getGambleHandler()->createRecord($player);
    }

    /**
     * @priority NORMAL
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $active = $this->core->getPlayerManager()->getGambleHandler()->getActiveCoinFlip($player);
        $coinFlip = $this->core->getPlayerManager()->getGambleHandler()->getCoinFlip($player);
        if($active !== null) {
            $active->finalRoll();
        }
        $this->core->getPlayerManager()->getGambleHandler()->removeCoinFlip($player);
    }
}