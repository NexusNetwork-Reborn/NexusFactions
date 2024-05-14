<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Area;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslationException;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\FlintSteel;
use pocketmine\item\Hoe;
use pocketmine\item\Shovel;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class AreaEvents implements Listener {

    /** @var Nexus */
    private $core;

    private array $lastArea = [];

    /**
     * AreaEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @priority HIGH
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            $this->lastArea[$player->getName()] = "Spawn";
            return;
        }
        if(!$player->isLoaded()) {
            return;
        }
        $to = $event->getTo();
        if($to->getY() < 0) {
            if($to->getWorld()->getFolderName() === $this->core->getServer()->getWorldManager()->getDefaultWorld()->getFolderName() or $to->getWorld()->getFolderName() === "bossarena") {
                $player->teleport($to->getWorld()->getSpawnLocation());
                return;
            }
        }
        $position = $player->getPosition();
        $area = $this->core->getServerManager()->getAreaHandler()->getAreaByPosition($position);
        if($area !== null) {
            $name = $area->getName();
            $color = match ($area->getPvpFlag()) {
                false => TextFormat::BOLD . TextFormat::GREEN,
                true => TextFormat::BOLD . TextFormat::RED
            };
            $type = match($area->getPvpFlag()) {
                false => "Safezone",
                true => "Warzone"
            };
            $description = match ($area->getPvpFlag()) {
                false => TextFormat::RESET . TextFormat::GRAY . "You are entering a non-pvp area.",
                true => TextFormat::RESET . TextFormat::GRAY . "You are entering a pvp area."
            };
            if ($name !== ($this->lastArea[$player->getName()] ?? "")) {
                $player->sendTitle($color . $type, $description);
                $this->lastArea[$player->getName()] = $name;
            }
        } 
        $oldClaim = $this->core->getPlayerManager()->getFactionHandler()->getClaimInPosition($event->getFrom());
        $newClaim = $this->core->getPlayerManager()->getFactionHandler()->getClaimInPosition($event->getTo());
        $oldFaction = $oldClaim === null ? null : $oldClaim->getFaction();
        $newFaction = $newClaim === null ? null : $newClaim->getFaction();
        if($oldFaction !== $newFaction) {
            if($newClaim === null) {
                $player->sendTitle(TextFormat::BOLD . TextFormat::GREEN . "Wilderness", TextFormat::RESET . TextFormat::GRAY . "You are entering a pvp area.");
            } else {
                $player->sendTitle(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . $newFaction->getName(), TextFormat::RESET . TextFormat::GRAY . "You are entering a claim."); // Todo: Add Description
            }
        }
    }

    /**
     * @priority LOWEST
     * @param PlayerExhaustEvent $event
     */
    public function onPlayerExhaust(PlayerExhaustEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($player->getPosition()->asPosition());
        if($area !== null) {
            if($area->getPvpFlag() === false) {
                $event->cancel();
                return;
            }
        }
    }

    /**
     * @priority HIGH
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($player->getPosition()->asPosition());
        if($area !== null) {
            if($area->getPvpFlag() === false) {
                if($item instanceof Hoe or $item instanceof Shovel or $item instanceof FlintSteel) {
                    $event->cancel();
                    return;
                }
                return;
            }
        }
    }

    /**
     * @priority LOWEST
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove2(PlayerMoveEvent $event): void {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        if(!$player->isLoaded()) {
            return;
        }
        if($player->getDataSession()->getRank()->getIdentifier() >= Rank::TRIAL_MODERATOR and $player->getDataSession()->getRank()->getIdentifier() <= Rank::OWNER) {
            return;
        }
        if($player->getWorld()->getFolderName() === Faction::CLAIM_WORLD or $player->getWorld()->getFolderName() === $this->core->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()) {
            return;
        }
        if($player->getAllowFlight() === false or $player->isFlying() === false) {
            return;
        }
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($player->getPosition()->asPosition());
        if($area !== null) {
            if($area->getPvpFlag() === false) {
                return;
            }
        }
    }

    /**
     * @priority LOWEST
     * @param BlockBreakEvent $event
     *
     * @throws TranslationException
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        if($player->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            return;
        }
        if($block->getPosition()->getY() <= 0) {
            $event->cancel();
            return;
        }
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($player->getPosition()->asPosition());
        if($area !== null) {
            if($area->getEditFlag() === false) {
                $player->sendMessage(Translation::getMessage("noPermission"));
                $event->cancel();
                return;
            }
        }
    }

    /**
     * @priority LOWEST
     * @param BlockPlaceEvent $event
     *
     * @throws TranslationException
     */
    public function onBlockPlace(BlockPlaceEvent $event): void {
        $block = $event->getBlockAgainst();
        $blockpos = $block->getPosition();
        $player = $event->getPlayer();
        if($player->getServer()->isOp($player->getName())) {
            return;
        }
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($blockpos);
        if($area !== null) {
            if($area->getEditFlag() === false) {
                $player->sendMessage(Translation::getMessage("noPermission"));
                $event->cancel();
                return;
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param EntityDamageEvent $event
     * @handleCancelled
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if(!$entity instanceof NexusPlayer) {
            return;
        }
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($entity->getPosition()->asPosition());
        if($area !== null) {
            if($area->getPvpFlag() === false) {
                $event->cancel();
                return;
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param ProjectileLaunchEvent $event
     */
    public function onProjectileLaunch(ProjectileLaunchEvent $event): void {
        $entity = $event->getEntity();
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $area = $areaManager->getAreaByPosition($entity->getPosition()->asPosition());
        if($area !== null) {
            if($area->getPvpFlag() === false) {
                $event->cancel();
                return;
            }
        }
    }

    /**
    * @priority LOWEST
    * @param BlockSpreadEvent $event
    */
    public function onBlockSpread(BlockSpreadEvent $event) {
        $block = $event->getBlock();
        $areaManager = $this->core->getServerManager()->getAreaHandler();
        $areas = $areaManager->getAreaByPosition($block->getPosition());
        if($areas !== null) {
            foreach($areas as $area) {
                if($area->getEditFlag() === false) {
                    $event->cancel();
                    return;
                }
            }
        }
    }

    /**
    * @priority HIGH
    * @param PlayerBucketEvent $event
    */
    public function onBucket(PlayerBucketEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($player->getWorld()->getDisplayName() === Faction::CLAIM_WORLD) {
            return;
        }
        $event->cancel();
    }

    /**
    * @priority HIGH
    * @param BlockBurnEvent $event
    */
    public function onBlockBurn(BlockBurnEvent $event) {
        $block = $event->getBlock();
        if($block->getPosition()->getWorld()->getDisplayName() === Faction::CLAIM_WORLD) {
            return;
        }
        $event->cancel();
    }
}