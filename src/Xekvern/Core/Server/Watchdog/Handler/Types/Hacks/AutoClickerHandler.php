<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types\Hacks;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Watchdog\Handler\Handler;
use pocketmine\event\Event;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class AutoClickerHandler extends Handler {

    /** @var int */
    private $autoClickTime;

    /**
     * AutoClickerHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        parent::__construct($core);
        $this->autoClickTime = time();
    }

    /**
     * @param NexusPlayer $player
     * @param Event $event
     */
    public function check(NexusPlayer $player, Event $event): void {
        if($event instanceof DataPacketReceiveEvent) {
            $packet = $event->getPacket();
            if($packet instanceof InventoryTransactionPacket) {
                if($packet->trData->getTypeId() === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
                    ++$player->cps;
                    $highest = 35;
                    if($player->getNetworkSession()->getPing() >= 200) {
                        $highest += (int)($player->getNetworkSession()->getPing() * 0.025); // This will increase cps high count 
                    }
                    if($this->autoClickTime < time()) {
                        $multiplier = time() - $this->autoClickTime;
                        $cps = floor($player->cps / $multiplier);
                        if($cps >= $highest) {
                            $reason = "Auto-clicking. CPS: $cps";
                            if($this->handleViolations($player, $reason)) {
                                $event->cancel();
                            }
                            $player->cps = 0;
                            return;
                        }
                        $this->autoClickTime = time();
                    }
                }
            }
        }
    }
}