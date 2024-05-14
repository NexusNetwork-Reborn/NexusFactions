<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Koth\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\Messages\TranslatonException;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class StartKOTHGameTask extends Task {

    /** @var Nexus */
    private $core;

    /** @var int */
    private $time = 60;

    /**
     * StartKOTHGameTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param int $currentTick
     *
     * @throws TranslatonException
     */
    public function onRun(): void {
        if($this->time >= 0) {
            if($this->time % 10 == 0 or $this->time <= 5) {
                $this->core->getServer()->broadcastMessage(TextFormat::BOLD . TextFormat::DARK_BLUE . "[King of The Hill] " . TextFormat::RESET . TextFormat::YELLOW . "A KOTH event will commence in " . $this->time . " seconds!");
            }
            $this->time--;
            return;
        }
        $this->core->getPlayerManager()->getCombatHandler()->startKOTHGame();
        $this->getHandler()->cancel();
    }
}