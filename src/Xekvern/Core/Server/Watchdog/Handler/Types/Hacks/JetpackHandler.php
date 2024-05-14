<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types\Hacks;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Watchdog\Handler\Handler;
use Xekvern\Core\Utils\MathUtils;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;

class JetpackHandler extends Handler {

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    public function check(NexusPlayer $player, Event $event): void {
        if($event instanceof PlayerMoveEvent) {
            $to = $event->getTo();
            $from = $event->getFrom();
            if($player->isSpectator() === true) {
                return;
            }
            if($player->hasVanished()) {
                return;
            }
            if(MathUtils::getFallDistance($from, $to) <= 4.2) {
                return;
            }
            $distance = abs($to->distance($from));
            if($distance >= 2.5) {
                $ping = $player->getNetworkSession()->getPing();
                if($ping < 200) {
                    $reason = "Jetpack. Distance: $distance";
                    $this->handleViolations($player, $reason);
                }
            }
        }
    }

    /**
     * @param NexusPlayer $player
     * @param string $cheat
     *
     * @return bool
     */
    public function handleViolations(NexusPlayer $player, string $cheat): bool {
        if(isset($this->violationTimes[$player->getUniqueId()->toString()])) {
            if(time() === $this->violationTimes[$player->getUniqueId()->toString()]) {
                return false;
            }
        }
        if(!isset($this->violations[$player->getUniqueId()->toString()])) {
            $this->violations[$player->getUniqueId()->toString()] = 0;
        }
        if($this->violations[$player->getUniqueId()->toString()]++ % 10 == 0) {
            $this->alert($player, $cheat);
            $this->violationTimes[$player->getUniqueId()->toString()] = time();
        }
        return true;
    }
}