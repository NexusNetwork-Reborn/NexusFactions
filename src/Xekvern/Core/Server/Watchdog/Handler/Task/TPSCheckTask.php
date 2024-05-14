<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Task;

use Xekvern\Core\Nexus;
use pocketmine\scheduler\Task;

class TPSCheckTask extends Task {

    /** @var Nexus */
    private $core;

    /**
     * TPSCheckTask constructor.
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
        $handlerManager = $this->core->getServerManager()->getWatchdogHandler()->getHandlerManager();
        if($this->core->getServer()->getTicksPerSecondAverage() > 18) {
            if($handlerManager->isHalted()) {
                $handlerManager->setHalted(false);
            }
            return;
        }
        if(!$handlerManager->isHalted()) {
            $handlerManager->setHalted(true);
        }
    }
}