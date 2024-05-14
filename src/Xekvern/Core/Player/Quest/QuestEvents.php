<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Quest;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Price\Event\ItemBuyEvent;
use Xekvern\Core\Server\Price\Event\ItemSellEvent;

class QuestEvents implements Listener {

    /** @var Nexus */
    private $core;

    /**
     * QuestEvents constructor.
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @priority LOWEST
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $this->core->getPlayerManager()->getQuestHandler()->addSession($player);
        $session = $this->core->getPlayerManager()->getQuestHandler()->getSession($player);
        foreach ($this->core->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
            if ($session->getQuestProgress($quest) === null) {
                $session->addQuestProgress($quest);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerDeathEvent $event
     */
    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            if ($cause->getDamager() instanceof NexusPlayer) {
                foreach ($this->core->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
                    if ($quest->getEventType() === Quest::KILL) {
                        $callable = $quest->getCallable();
                        $callable($cause);
                    }
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }
        foreach ($this->core->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
            if ($quest->getEventType() === Quest::DAMAGE) {
                $callable = $quest->getCallable();
                $callable($event);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }
        foreach ($this->core->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
            if ($quest->getEventType() === Quest::BREAK) {
                $callable = $quest->getCallable();
                $callable($event);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }
        foreach ($this->core->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
            if ($quest->getEventType() === Quest::PLACE) {
                $callable = $quest->getCallable();
                $callable($event);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param ItemSellEvent $event
     */
    public function onItemSell(ItemSellEvent $event): void {
        foreach ($this->core->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
            if ($quest->getEventType() === Quest::SELL) {
                $callable = $quest->getCallable();
                $callable($event);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param ItemBuyEvent $event
     */
    public function onItemBuy(ItemBuyEvent $event): void {
        foreach ($this->core->getPlayerManager()->getQuestHandler()->getActiveQuests() as $quest) {
            if ($quest->getEventType() === Quest::BUY) {
                $callable = $quest->getCallable();
                $callable($event);
            }
        }
    }
}