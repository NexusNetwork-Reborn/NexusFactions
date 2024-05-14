<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\NexusException;
use Xekvern\Core\Provider\Event\PlayerLoadEvent;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Server\World\Tile\Generator;
use pocketmine\block\tile\Container;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\permission\DefaultPermissionNames;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class FactionEvents implements Listener {

    /** @var Nexus */
    private $core;

    /**
     * FactionEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @priority HIGHEST
     * @param PlayerLoadEvent $event
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $faction = $player->getDataSession()->getFaction();
        if($faction === null) {
            return;
        }
        foreach($faction->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::GREEN . "{$player->getName()} is now online!");
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $faction = $player->getDataSession()->getFaction();
        if($faction === null) {
            return;
        }
        if($faction->needsUpdate()) {
            $faction->updateAsync();
        }
        foreach($faction->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::RED . "{$player->getName()} is now offline!");
        }
    }

    /**
     * @param PlayerInteractEvent $event
     *
     * @throws TranslatonException
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $claim = $this->core->getPlayerManager()->getFactionHandler()->getClaimInPosition($block->getPosition());
        if($claim === null) {
            return;
        }
        $faction = $player->getDataSession()->getFaction();
        $block = $event->getBlock();
        if($block instanceof Container) {
            if($faction !== null) {
                if($claim->getFaction()->getName() !== $faction->getName()) {
                    $player->sendMessage(Translation::getMessage("editClaimNotAllowed"));
                    $event->cancel();
                }
                else {
                    if(!$faction->getPermissionsModule()->hasPermission($player, PermissionsModule::PERMISSION_ACCESS_CONTAINERS)) {
                        $player->sendMessage(Translation::getMessage("editClaimNotAllowed"));
                        $event->cancel();
                    }
                }
            }
        }
    }

    /**
     * @param PlayerMoveEvent $event
     *
     * @throws NexusException
     */
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $from = $event->getFrom();
        $to = $event->getTo();
        if($player->isUsingFMapHUD() === true) {
            $chunkX = (int)$from->getX() >> 4;
            $chunkZ = (int)$from->getZ() >> 4;
            $nextChunkX = (int)$to->getX() >> 4;
            $nextChunkZ = (int)$to->getZ() >> 4;
            $lines = FactionHandler::sendFactionMap($player);
            $scoreboard = $player->getScoreboard();
            if($chunkX !== $nextChunkX or $chunkZ !== $nextChunkZ) {
                $i = 4;
                foreach($lines as $line) {
                    $scoreboard->setScoreLine($i++, $line);
                }
            }
            return;
        }
    }

    /**
     * @priority LOWEST
     * @param EntityDamageEvent $event
     *
     * @throws TranslatonException
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if($entity instanceof NexusPlayer and $entity->isLoaded()) {
            $faction = $entity->getDataSession()->getFaction();
            if($faction === null) {
                return;
            }
            if($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                if(!$damager instanceof NexusPlayer) {
                    return;
                }
                if(!$damager->isLoaded()) {
                    return;
                }
                $damagerFaction = $damager->getDataSession()->getFaction();
                if($damagerFaction === null) {
                    return;
                }
                if($faction->isInFaction($damager->getName()) or $faction->isAlly($damagerFaction)) {
                    $damager->sendMessage(Translation::getMessage("attackFactionAssociate"));
                    $event->cancel();
                    return;
                }
            }
        }
    }

    /**
     * @priority LOWEST
     *
     * @param BlockPlaceEvent $event
     *
     * @throws TranslatonException
     */
    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($player->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            return;
        }
        $claim = $this->core->getPlayerManager()->getFactionHandler()->getClaimInPosition($block->getPosition());
        if($claim === null) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $faction = $player->getDataSession()->getFaction();
        if($faction !== null) {
            if($claim->getFaction()->getName() === $faction->getName()) {
                if($faction->getPermissionsModule()->hasPermission($player, PermissionsModule::PERMISSION_EDIT_CLAIMS)) {
                    return;
                }
            }
        }
        $player->sendMessage(Translation::getMessage("editClaimNotAllowed"));
        $event->cancel();
    }

    /**
     * @priority LOWEST
     * @param BlockBreakEvent $event
     *
     * @throws TranslatonException
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $level = $player->getWorld();
        if($level === null) {
            return;
        }
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($player->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            return;
        }
        $claim = $this->core->getPlayerManager()->getFactionHandler()->getClaimInPosition($block->getPosition());
        if($claim === null) {
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $faction = $player->getDataSession()->getFaction();
        if($faction !== null) {
            if($claim->getFaction()->getName() === $faction->getName()) {
                if($faction->getPermissionsModule()->hasPermission($player, PermissionsModule::PERMISSION_EDIT_CLAIMS)) {
                    $tile = $level->getTile($block->getPosition());
                    if($tile instanceof Generator) {
                        $bb = $player->getBoundingBox()->expandedCopy(200, 200, 200);
                        $allow = true;
                        foreach($level->getNearbyEntities($bb) as $e) {
                            if($e instanceof NexusPlayer) {
                                if(!$e->isLoaded()) {
                                    continue;
                                }
                                $fac = $e->getDataSession()->getFaction();
                                if($fac === null) {
                                    return;
                                }
                                if($fac->isInFaction($player->getName())) {
                                   continue;
                                }
                                $allow = false;
                            }
                        }
                        if(!$allow) {
                            $event->cancel();
                            $player->sendMessage(Translation::getMessage("enemiesNearBy"));
                            return;
                        }
                    }
                    return;
                }
            }
        }
        $player->sendMessage(Translation::getMessage("editClaimNotAllowed"));
        $event->cancel();
    }
}