<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Update\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\scheduler\Task;

class SaveTask extends Task {

    /** @var Nexus */
    private $core;

    /**
     * SaveTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param int $tick
     */
    public function onRun(): void {
        $start = microtime(true);
        /** @var NexusPlayer $player */
        foreach($this->core->getServer()->getOnlinePlayers() as $player) {
            if($player->isLoaded()) {
                $player->getDataSession()->saveDataAsync();
            }
        }
        $time = (microtime(true) - $start);
        $this->core->getLogger()->notice("[Auto Save] Save completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
    }
}