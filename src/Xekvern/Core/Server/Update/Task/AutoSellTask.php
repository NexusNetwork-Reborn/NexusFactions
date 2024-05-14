<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Update\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\scheduler\Task;

class AutoSellTask extends Task {

    /** @var Nexus */
    private $core;

    /** @var NexusPlayer[] */
    private $players;

    /**
     * UpdateTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->players = $core->getServer()->getOnlinePlayers();
    }

    /**
     * @param int $tick
     */
    public function onRun(): void {
        if(empty($this->players)) {
            $this->players = $this->core->getServer()->getOnlinePlayers();
        }
        $player = array_shift($this->players);
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($player->isOnline() === false) {
            return;
        }
        if($player->isLoaded() === false) {
            return;
        }
        if($player->isAutoSelling() === false) {
            return;
        }
        $this->core->getServer()->dispatchCommand($player, "sell all");
    }
}