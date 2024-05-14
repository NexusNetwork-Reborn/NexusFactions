<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types\Hacks;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Server\Watchdog\Handler\Handler;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ReachHandler extends Handler {

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    public function check(NexusPlayer $player, Event $event): void {
        if($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if(!$entity instanceof NexusPlayer) {
                return;
            }
            if($event instanceof EntityDamageByChildEntityEvent) {
                return;
            }
            if($player->isCreative()) {
                return;
            }
            if($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
                return;
            }
            $distance = $player->getPosition()->distance($entity->getPosition());
            $amt = 6.0;
            if($player->getNetworkSession()->getPing() >= 200 or $entity->getNetworkSession()->getPing() >= 200) {
                $highest = ($player->getNetworkSession()->getPing() > ($entity instanceof NexusPlayer) ? $entity->getNetworkSession()->getPing() : -1) ? $player->getNetworkSession()->getPing() : $entity->getNetworkSession()->getPing();
                $amt += ($highest * 0.006);
            }
            $pearlHandler = $this->core->getServerManager()->getWatchdogHandler()->getHandlerManager()->getPearlHandler();
            $throws = [$pearlHandler->getMostRecentThrowFrom($player->getName())];
            if($entity instanceof NexusPlayer) {
                $throws[] = $pearlHandler->getMostRecentThrowFrom($entity->getName());
            }
            foreach($throws as $throw) {
                if($throw !== null) {
                    if(!$throw->getLandingLocation()) {
                        return;
                    }
                    if($throw->getLandingTime() + 4 <= time()) {
                        $pos = $throw->getLandingLocation();
                        if($throw->getPlayer()->getPosition()->distance($pos) >= 20) {
                            return;
                        }
                    }
                }
            }
            if($distance > $amt) {
                $reason = "Reach. Distance: " . round($distance, 1);
                $this->handleViolations($player, $reason);
                $event->cancel();
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
        if(++$this->violations[$player->getUniqueId()->toString()] >= self::VIOLATION_LIMIT) {
            $this->violations[$player->getUniqueId()->toString()] = 0;
            $this->core->getScheduler()->scheduleDelayedTask(new class($player, $cheat) extends Task {

                /** @var NexusPlayer */
                private $player;

                /** @var string */
                private $cheat;

                /**
                 *  constructor.
                 *
                 * @param NexusPlayer $player
                 * @param string $cheat
                 */
                public function __construct(NexusPlayer $player, string $cheat) {
                    $this->player = $player;
                    $this->cheat = $cheat;
                }

                /**
                 * @param int $currentTick
                 */
                public function onRun(): void {
                    if($this->player->isOnline() === false) {
                        return;
                    }
                    Server::getInstance()->broadcastMessage(Translation::getMessage("antiCheatKickBroadcast", [
                        "name" => TextFormat::RED . $this->player->getName(),
                        "reason" => TextFormat::YELLOW . $this->cheat
                    ]));
                    $this->player->close(null, Translation::getMessage("antiCheatKickMessage", [
                        "reason" => TextFormat::YELLOW . $this->cheat
                    ]));
                }
            }, 20);
        }
        elseif($this->violations[$player->getUniqueId()->toString()]++ % 5 == 0) {
            $this->alert($player, $cheat);
            $this->violationTimes[$player->getUniqueId()->toString()] = time();
        }
        return true;
    }
}