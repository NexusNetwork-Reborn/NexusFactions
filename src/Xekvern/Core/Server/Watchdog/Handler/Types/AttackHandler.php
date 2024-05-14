<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Types;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;

class AttackHandler implements Listener {

    /** @var Nexus */
    private $core;

    /** @var mixed[] */
    private static $attacks = [];

    /**
     * AttackHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $core->getServer()->getPluginManager()->registerEvents($this, $core);
        $this->core = $core;
    }

    /**
     * Returns the most recent attack from an entity with the given ID
     *
     * @param int $id - Entity Runtime ID
     *
     * @return array|null - Hit data or nothing
     */
    public static function getLastAttack(int $id): ?array {
        if(!isset(self::$attacks[$id])) {
            return null;
        }
        else {
            return self::$attacks[$id][count(self::$attacks[$id]) - 1];
        }
    }

    /**
     * Returns the most recent attack time from an entity with the given ID
     *
     * @param int $id - Entity Runtime ID
     *
     * @return int
     */
    public static function getLastAttackTime(int $id): int {
        return (self::getLastAttack($id) === null) ? -1 : self::getLastAttack($id)['time'];
    }

    /**
     * Returns the most recent damaged entity with the given ID
     * @param int $id - Entity Runtime ID
     * @return array|null
     */
    public static function getLastDamage(int $id): ?array {
        $recent = null;
        foreach(self::$attacks as $damager => $attacks) {
            foreach($attacks as $attack) {
                if($attack['entity'] === $id) {
                    if($recent === null) {
                        $recent = [
                            'time' => $attack['time'],
                            'id' => $damager,
                            'player' => $attack['player']
                        ];
                        continue;
                    }
                    else {
                        if($recent['time'] < $attack['time']) {
                            $recent = [
                                'time' => $attack['time'],
                                'id' => $damager,
                                'player' => $attack['player']
                            ];
                            continue;
                        }
                    }
                }
            }
        }
        return $recent;
    }

    /**
     * Returns the most recent damage time from an entity with the given ID
     *
     * @param int $id - Entity Runtime ID
     *
     * @return array|null
     */
    public static function getLastDamageTime(int $id): int {
        return (self::getLastDamage($id) === null) ? -1 : self::getLastDamage($id)['time'];
    }

    /**
     * PM Event, triggered when an entity is damaged by another
     *
     * @param EntityDamageByEntityEvent $event - Event
     *
     * @return void
     */
    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void {
        $damager = $event->getDamager();
        if($damager === null or $damager->isClosed()) {
            return;
        }
        $victim = $event->getEntity();
        if($victim === null or $victim->isClosed()) {
            return;
        }
        // This does NOT care about entity names, and carries entity runtime ids
        if(!isset(self::$attacks[$damager->getId()])) {
            self::$attacks[$damager->getId()] = [];
        }
        $victim->resetFallDistance();
        array_push(self::$attacks[$damager->getId()], [
            "time" => microtime(true),
            "player" => ($damager instanceof NexusPlayer),
            "entity" => $victim->getId(),
            "entityPlayer" => ($victim instanceof NexusPlayer)
        ]);
        $this->purgeOld();
    }

    /**
     * Purges old attack data (2 seconds or older)
     * @return void
     */
    private function purgeOld(): void {
        foreach(self::$attacks as $damager => $attacks) {
            foreach($attacks as $index => $attack) {
                if(!isset(self::$attacks[$damager])) {
                    return;
                }
                if($attack['time'] + 2 <= microtime(true)) {
                    array_splice(self::$attacks[$damager], $index);
                }
                if(empty(self::$attacks[$damager])) {
                    unset(self::$attacks[$damager]);
                    break;
                }
            }
        }
    }
}