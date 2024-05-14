<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types\Hacks;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Watchdog\Handler\Handler;
use Xekvern\Core\Server\Watchdog\Handler\Types\AttackHandler;
use Xekvern\Core\Utils\LevelUtils;
use Xekvern\Core\Utils\MathUtils;
use Xekvern\Core\Utils\PlayerCalculate;
use pocketmine\block\Slab;
use pocketmine\block\SnowLayer;
use pocketmine\block\Stair;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

class FlyHandler extends Handler {

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    public function check(NexusPlayer $player, Event $event): void {
        if($event instanceof PlayerMoveEvent) {
            $to = clone $event->getTo();
            $from = clone $event->getFrom();
            $distance = $to->distance($from);
            if($player->getAllowFlight() === true) {
                return;
            }
            if($player->isCreative() === true) {
                return;
            }
            if($player->isSpectator() === true) {
                return;
            }
            if($player->hasVanished()) {
                return;
            }
            if(!$player->isLoaded()) {
                return;
            }
            $level = $player->getWorld();
            if($level->getFolderName() === "bossarena") {
                return;
            }
            if(LevelUtils::getRelativeBlock(LevelUtils::getBlockWhere($player->getPosition()), Facing::UP)->getTypeId() === 0) {
                $blockAtPlayer = LevelUtils::getBlockWhere($player->getPosition());
                $blockBelow = LevelUtils::getRelativeBlock($blockAtPlayer, Facing::DOWN);
                if($blockBelow instanceof Slab or $blockBelow instanceof Stair or $blockBelow instanceof SnowLayer) {
                    return;
                }
                if(MathUtils::getFallDistance($from, $to) === 0) {
                    if($distance > 0.25) {
                        $reason = "Flying";
                        if($this->handleViolations($player, $reason)) {
                            $event->cancel();
                        }
                        return;
                    }
                }
                if(LevelUtils::getRelativeBlock(LevelUtils::getBlockWhere($player->getPosition()), Facing::DOWN)->getTypeId() === 0) {
                    $realtive = LevelUtils::getRelativeBlock(LevelUtils::getBlockWhere($player->getPosition()), Facing::DOWN);
                    if(LevelUtils::getRelativeBlock($realtive, Facing::DOWN)->getTypeId() === 0) {
                        $square = PlayerCalculate::getSurroundings($player);
                        $lastDamageTime = AttackHandler::getLastDamageTime($player->getId());
                        $allowed = ($lastDamageTime === -1) ? 120 : ((microtime(true) - $lastDamageTime) * 40);
                        if($player->getInAirTicks() <= $allowed) {
                            return;
                        }
                        foreach($square as $point) {
                            if($point->getId() !== 0) {
                                return;
                            }
                        }
                        if(MathUtils::getFallDistance($from, $to) <= 0) {
                            $reason = "Flying";
                            $this->handleViolations($player, $reason);
                        }
                    }
                }
            }
        }
    }
}