<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\NPC;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use Xekvern\Core\Provider\Event\PlayerLoadEvent;

class NPCEvents implements Listener
{

    /** @var Nexus */
    private $core;

    /**
     * NPCEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
    }

    /**
     * @priority NORMAL
     *
     * @param PlayerLoadEvent $event
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        foreach ($this->core->getServerManager()->getNPCHandler()->getNPCs() as $npc) {
            if (!$npc->isSpawned($player)) {
                $npc->spawnTo($player);
            }
            //try {
            //    if (!$npc->isSpawned($player)) {
            //        $npc->spawnTo($player);
            //    }
            //} catch (\LogicException) {
            //    return;
            //}
        }
    }

    /**
     * @priority NORMAL
     *
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event)
    {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        if (!$player->isLoaded()) {
            return;
        }
        foreach ($this->core->getServerManager()->getNPCHandler()->getNPCs() as $npc) {
            if ($npc->getPosition()->getWorld() === null or $player->getWorld() === null) {
                continue;
            }
            if ($npc->getPosition()->getWorld()->getFolderName() === $player->getWorld()->getFolderName()) {
                if ($npc->getPosition()->distance($player->getPosition()) <= 20) {
                    $npc->move($player);
                }
            }
        }
    }

    /**
     * @priority NORMAL
     *
     * @param DataPacketReceiveEvent $event
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $pk = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if ($player === null) {
            return;
        }
        if ($pk instanceof InventoryTransactionPacket and $pk->trData instanceof UseItemOnEntityTransactionData) {
            $npc = $this->core->getServerManager()->getNPCHandler()->getNPC($pk->trData->getActorRuntimeId());
            if ($npc === null) {
                return;
            }
            $npc->tap($player);
        }
    }
}
