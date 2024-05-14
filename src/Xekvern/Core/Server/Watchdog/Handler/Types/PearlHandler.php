<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types;

use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Watchdog\Handler\Event\PearlThrowEvent;
use Xekvern\Core\Server\Watchdog\Handler\Object\PearlThrow;
use Xekvern\Core\Server\Watchdog\Handler\Task\PearlPurgeTask;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDespawnEvent;

class PearlHandler implements Listener
{

    /** @var PearlThrow[] */
    private $throws = [];

    /** @var Nexus */
    private $core;

    /**
     * PearlHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $core->getServer()->getPluginManager()->registerEvents($this, $core);
        $core->getScheduler()->scheduleRepeatingTask(new PearlPurgeTask($this), 20 * 10);
        $this->core = $core;
    }

    /**
     * @param PearlThrowEvent $event
     */
    public function onPearlThrown(PearlThrowEvent $event): void
    {
        $player = $event->getPlayer();
        $entity = $event->getEntity();
        if ($event->isCancelled()) {
            return;
        }
        $throw = new PearlThrow($player, $entity->getId());
        $this->throws[] = $throw;
        return;
    }

    /**
     * Triggered when a entity is despawned
     *
     * @param EntityDespawnEvent $event - EntityDespawnEvent
     *
     * @return void
     */
    public function onEntityDespawn(EntityDespawnEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Projectile) {
            $id = $entity->getId();
            foreach ($this->throws as &$throw) {
                if ($throw->getPearlEntityId() === $id) {
                    $throw->setCompleted((int)microtime(true), $event->getEntity()->getPosition());
                }
            }
        }
    }

    /**
     * Clears throws from memory for performance reasons.
     * @return void
     */
    public function purge(): void
    {
        $purged = 0;
        foreach ($this->throws as &$throw) {
            if ($throw->getLandingTime() + 10 <= time()) {
                unset($this->throws[array_search($throw, $this->throws)]);
                $purged++;
            }
        }
        return;
    }

    /**
     * Returns a list of throws from a player
     *
     * @param String $player - Player name
     *
     * @return PearlThrow[] - Array of throws
     */
    public function getThrowsFrom(string $player): array
    {
        $allThrows = [];
        foreach ($this->throws as $throw) {
            if ($throw->getPlayer()->getName() === $player) {
                $allThrows[] = $throw;
            }
        }
        return $allThrows;
    }

    /**
     * Returns the most recent throw from a player
     *
     * @param String $player - Player name
     *
     * @return PearlThrow|Null - The most recent thrown pearl from player
     */
    public function getMostRecentThrowFrom(string $player): ?PearlThrow
    {
        $throws = $this->getThrowsFrom($player);
        $recent = null;
        foreach ($throws as $throw) {
            if (!$recent) {
                $recent = $throw;
                continue;
            }
            if ($recent->getThrownTime() > $throw->getThrownTime()) {
                $recent = $throw;
                continue;
            }
        }
        return $recent;
    }
}