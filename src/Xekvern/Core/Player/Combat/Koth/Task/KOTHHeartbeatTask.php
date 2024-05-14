<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Koth\Task;

use Xekvern\Core\Translation\TranslatonException;
use pocketmine\scheduler\Task;
use Xekvern\Core\Player\Combat\CombatHandler;

class KOTHHeartbeatTask extends Task {

    /** @var CombatHandler */
    private $handler;

    /**
     * KOTHHeartbeatTask constructor.
     *
     * @param CombatHandler $handler
     */
    public function __construct(CombatHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $currentTick
     *
     * @throws TranslatonException
     */
    public function onRun(): void {
        $game = $this->handler->getKOTHGame();
        if($game !== null and $game->hasStarted()) {
            $game->tick();
        }
    }
}
