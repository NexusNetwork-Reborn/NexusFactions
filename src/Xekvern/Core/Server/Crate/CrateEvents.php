<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Provider\Event\PlayerLoadEvent;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\nbt\NoSuchTagException;
use pocketmine\utils\TextFormat;

class CrateEvents implements Listener {

    /** @var Nexus */
    private $core;

    /** @var int */
    protected int $spam = 0;

    /**
     * CrateEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param PlayerLoadEvent $event
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        foreach($this->core->getServerManager()->getCrateHandler()->getCrates() as $crate) {
            $crate->spawnTo($player);
        }
    }

    /**
     * @priority LOWEST
     * @param PlayerInteractEvent $event
     *
     * @throws TranslatonException
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $block = $event->getBlock();
        foreach($this->core->getServerManager()->getCrateHandler()->getCrates() as $crate) {
            if($crate->getPosition()->equals($block->getPosition())) {
                $particle = $player->getFloatingText($crate->getName());
                if($particle === null) {
                    $crate->spawnTo($player);
                }
                if ((time() - $this->spam) > 2) {
                    $crate->try($player);
                    $this->spam = time();
                } else {
                    $player->sendTip(TextFormat::RED . "On Cooldown!");
                }
                $event->cancel();
            }
        }
    }

    /**
     * @priority LOWEST
     * @param PlayerMoveEVent $event
     * 
     * @throws TranslationException
     * This is to fix floating text not showing up sometimes.
     */
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($player->isLoaded()) {
            foreach($this->core->getServerManager()->getCrateHandler()->getCrates() as $crate) {
                $particle = $player->getFloatingText($crate->getName());
                if($particle === null) {
                    $crate->spawnTo($player);
                }
            }
        }
    }

    /**
     * @param EntityItemPickupEvent $event
     * @return void
     * This is to fix being able to pickup Animation Items
     */
    public function onPickup(EntityItemPickupEvent $event): void {
        $item = $event->getItem();
        try {
            if($item->getNamedTag()->getString("CrateAnimation") === "true" && $item->getNamedTag()->getInt("Created") - time() >= 7) {
                foreach ($event->getOrigin()->getWorld()->getEntities() as $entity) {
                    if ($entity instanceof ItemEntity && $entity->getItem() === $item) {
                        $entity->flagForDespawn();
                    }
                }
            }
            $item = $item->setCount(0);
            $event->setItem($item);
            $event->cancel();
        } catch (NoSuchTagException) {
            
        }
    }
}