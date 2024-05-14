<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Announcement\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslationException;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class RestartTask extends Task {

    /** @var Nexus */
    private $core;

    /** @var int */
    private $time = 10800;

    /**
     * RestartTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param int $currentTick
     *
     * @throws TranslationException
     */
    public function onRun(): void {
        $hours = floor($this->time / 3600);
        $minutes = (int)($this->time / 60) % 60;
        $seconds = $this->time % 60;
        if ($hours < 1) {
            $countdownTimes = [59, 30, 15, 10, 5, 4, 3, 2, 1];
            foreach ($countdownTimes as $countdown) {
                if ($minutes == 0 && $seconds == $countdown) {
                    foreach ($this->core->getServer()->getOnlinePlayers() as $player) {
                        if (!$player instanceof NexusPlayer) {
                            continue;
                        }
                        $player->sendMessage(TextFormat::RED . "Server restarting in $countdown seconds!");
                        $player->sendTitle(TextFormat::BOLD . TextFormat::YELLOW . "RESTARTING", TextFormat::GRAY . "Server restarting in $countdown seconds!");
                    }
                    break;
                }
            }
            if($minutes == 0 and $seconds == 5) {
                foreach($this->core->getServer()->getOnlinePlayers() as $player) {
                    if(!$player instanceof NexusPlayer) {
                        continue;
                    }
                    $player->removeCurrentWindow();
                }
            }
            if($minutes == 0 and $seconds == 0) {
                foreach($this->core->getServer()->getWorldManager()->getWorlds() as $level) {
                    $level->save(true);
                }
                foreach($this->core->getServer()->getOnlinePlayers() as $player) {
                    if($player instanceof NexusPlayer) {
                        if($player->isLoaded()) {
                            $player->getDataSession()->saveData();
                        }
                    }
                }
                foreach($this->core->getPlayerManager()->getFactionHandler()->getFactions() as $faction) {
                    $faction->update();
                }
                foreach($this->core->getPlayerManager()->getFactionHandler()->getClaims() as $claim) {
                    $claim->update();
                }
                foreach($this->core->getServer()->getOnlinePlayers() as $player) {
                    if(!$player instanceof NexusPlayer) {
                        continue;
                    }
                    if($player->isTagged()) {
                        $player->combatTag(false);
                        $player->setCombatTagged(false);
                    }
                    $player->transfer("play.nexuspe.net", 19132, TextFormat::RESET . TextFormat::RED . "Server is restarting...");
                }
                $this->core->getServer()->shutdown();
            }
        }
        $this->time--;
    }

    /**
     * @param int $time
     */
    public function setRestartProgress(int $time): void {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getRestartProgress(): int {
        return $this->time;
    }
}