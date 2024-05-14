<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Outpost\Task;

use Xekvern\Core\Player\Combat\CombatHandler;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\scheduler\Task;

class OutpostHeartbeatTask extends Task {

    /** @var CombatHandler */
    private $handler;

    /**
     * OutpostHeartbeatTask constructor.
     *
     * @param CombatManager $manager
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
        $game = $this->handler->getOutpostArena();
        $game->tick();
    }
}
