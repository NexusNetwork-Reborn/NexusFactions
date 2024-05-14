<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types\Hacks;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Watchdog\Handler\Handler;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;

class InstantBreakHandler extends Handler {

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    public function check(NexusPlayer $player, Event $event): void {
        if($event instanceof BlockBreakEvent) {
            if(!$event->getInstaBreak()) {
                $handler = $this->core->getServerManager()->getWatchdogHandler()->getHandlerManager()->getBreakHandler();
                $time = $handler->getBreakTime($player);
                if($time === null) {
                    return;
                }
                $target = $event->getBlock();
                $item = $event->getItem();
                $expectedTime = ceil($target->getBreakInfo()->getBreakTime($item) * 20);
                $amount = 1;
                if($this->core->getServer()->getTicksPerSecond() < 19) {
                    $amount = 20 - floor($this->core->getServer()->getTicksPerSecond());
                }
                $expectedTime -= $amount;
                if($expectedTime < 20) {
                    return;
                }
                $actualTime = ceil(microtime(true) * 20) - $time;
                if($actualTime < $expectedTime) {
                    if(($expectedTime - $actualTime) > 20) {
                        $expectedSeconds = $expectedTime * 0.05;
                        $actualSeconds = $actualTime * 0.05;
                        $reason = "Insta-breaking. Expected time: $expectedSeconds" . "s, Actual time: $actualSeconds" . "s";
                        $this->handleViolations($player, $reason);
                        $event->cancel();
                    }
                }
            }
        }
    }
}