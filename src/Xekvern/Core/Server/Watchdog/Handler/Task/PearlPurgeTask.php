<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Task;

use Xekvern\Core\Server\Watchdog\Handler\Types\PearlHandler;
use pocketmine\scheduler\Task;

class PearlPurgeTask extends Task {

    /** @var PearlHandler */
    private $handler;

    /**
     * PearlPurgeTask constructor.
     *
     * @param PearlHandler $handler
     */
    public function __construct(PearlHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param int $tick
     */
    public function onRun(): void {
        $this->handler->purge();
    }
}