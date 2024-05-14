<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types\Hacks;

use pocketmine\block\VanillaBlocks;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Watchdog\Handler\Handler;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\utils\TextFormat;

class NukeHandler extends Handler {

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    public function check(NexusPlayer $player, Event $event): void {
        if($event instanceof BlockBreakEvent) {
            if(!$event->getInstaBreak()) {
                $handler = $this->core->getServerManager()->getWatchdogHandler()->getHandlerManager()->getBreakHandler();
                $block = $event->getBlock();
                if ($block->getTypeId() === VanillaBlocks::LAPIS_LAZULI_ORE()->getTypeId()) {
                    return;
                }
                $blocksBroken = $handler->getBlocksBrokenOnClick($player);
                if($blocksBroken > 10) {
                    $reason = "Nuke. Many blocks broken in one action";
                    $this->handleViolations($player, $reason);
                    $event->cancel();
                }
            }
        }
    }
}