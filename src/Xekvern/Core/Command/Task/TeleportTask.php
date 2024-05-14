<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Task;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\EndermanTeleportSound;
use Xekvern\Core\Translation\Translation;

class TeleportTask extends Task {

    /** @var NexusPlayer */
    private $player;

    /** @var Position */
    private $position;

    /** @var Position */
    private $originalLocation;

    /** @var int */
    private $time;

    /** @var int */
    private $maxTime;

    /**
     * TeleportTask constructor.
     *
     * @param NexusPlayer $player
     * @param Position $position
     * @param int $time
     */
    public function __construct(NexusPlayer $player, Position $position, int $time) {
        $this->player = $player;
        if($player->hasPermission(DefaultPermissionNames::GROUP_OPERATOR) and $player->getGamemode() === GameMode::CREATIVE()) {
            $this->player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 200, 20));
            $player->teleport($position);
            $player->sendMessage(Translation::GREEN . "You have successfully been sent to your location.");
            $player->getWorld()->addSound($player->getEyePos(),new EndermanTeleportSound());
            $this->player = null;
            return;
        }
        $this->player->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), $time * 2 * 20, 1, false));
        $this->player->setTeleporting();
        $this->position = $position;
        $this->originalLocation = $player->getPosition();
        $this->time = $time;
        $this->maxTime = $time;

    }

    /**
     * @param int $currentTick
     */
    public function onRun(): void    {
        if($this->player === null or $this->player->isClosed()) {
            $this->getHandler()->cancel();
            return;
        }
        if($this->originalLocation->distance($this->player->getPosition()) >= 1) {
            $this->player->setTeleporting(false);
            $this->player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Failed to teleport", TextFormat::WHITE . "You must stand still!");
            $this->getHandler()->cancel();
            return;
        }
        if($this->time >= 0) {
            $times = ($this->maxTime - $this->time) % 6; 
            $this->player->sendTitle(
                TextFormat::BOLD . TextFormat::AQUA . "TELEPORTING", 
                TextFormat::GREEN . str_repeat("■", $times) . TextFormat::GRAY . str_repeat("■", 5 - $times)
            );
            if($times >= 1) { $this->player->broadcastSound(new ClickSound()); }
            $this->time--;
            return;
        }
        
        $this->player->teleport($this->position);
        $this->player->sendMessage(Translation::GREEN . "You have successfully been sent to your location.");
        $this->player->getWorld()->addSound($this->player->getEyePos(),new EndermanTeleportSound());
        $this->player->setTeleporting(false);
        $this->player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 200, 20));
        $this->getHandler()->cancel();
        return;
    }
}
