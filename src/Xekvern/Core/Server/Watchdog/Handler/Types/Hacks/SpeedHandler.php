<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types\Hacks;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Watchdog\handler\Handler;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class SpeedHandler extends Handler
{

    /** @var int[] */
    private $lastTime = [];

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    public function check(NexusPlayer $player, Event $event): void
    {
        if ($event instanceof PlayerMoveEvent) {
            if ($player->isSpectator() === true) {
                return;
            }
            if ($player->hasVanished()) {
                return;
            }
            $to = clone $event->getTo();
            $from = clone $event->getFrom();
            $lastTime = (isset($this->lastTime[$player->getUniqueId()->toString()])) ? $this->lastTime[$player->getUniqueId()->toString()] : -1;
            // Fix falling
            $from->y = 0;
            $to->y = 0;
            $distance = $to->distance($from);
            $allowed = 1;
            if ($player->getNetworkSession()->getPing() >= 200) {
                $allowed += ($player->getNetworkSession()->getPing() * 0.0009);
            }
            // .9 is way to lenient for the allowed movements
            if ($player->isCreative() || $player->isSpectator() || $player->isFlying() || $player->isGliding()) {
                return;
            }
            if ($player->getEffects()->has(VanillaEffects::SPEED()) !== null) {
                // Not tested fully, but definitely allows effects.
                //if ($player->getEffects()->get(VanillaEffects::SPEED())->getEffectLevel() !== 0) {
                    //$allowed = ($player->getEffects()->get(VanillaEffects::SPEED())->getEffectLevel() + 0.45);
                //}

                // TODO
            }
            // if the last MOVE packet sent was sent more than 2 secs ago
            if (microtime(true) >= $lastTime + 2) {
                return;
            }
            if ($distance >= $allowed) {
                $reason = "Speed";
                $this->handleViolations($player, $reason);
                return;
            }
        }
        if ($event instanceof DataPacketReceiveEvent) {
            $pk = $event->getPacket();
            $player = $event->getOrigin()->getPlayer();
            if ($pk instanceof MovePlayerPacket) {
                $this->lastTime[$player->getUniqueId()->toString()] = microtime(true);
            }
        }
    }

    /**
     * @param NexusPlayer $player
     * @param string $cheat
     *
     * @return bool
     */
    public function handleViolations(NexusPlayer $player, string $cheat): bool
    {
        if (isset($this->violationTimes[$player->getUniqueId()->toString()])) {
            if (time() === $this->violationTimes[$player->getUniqueId()->toString()]) {
                return false;
            }
        }
        if (!isset($this->violations[$player->getUniqueId()->toString()])) {
            $this->violations[$player->getUniqueId()->toString()] = 0;
        }
        if ($this->violations[$player->getUniqueId()->toString()]++ % 10 == 0) {
            $this->alert($player, $cheat);
            $this->violationTimes[$player->getUniqueId()->toString()] = time();
        }
        return true;
    }
}