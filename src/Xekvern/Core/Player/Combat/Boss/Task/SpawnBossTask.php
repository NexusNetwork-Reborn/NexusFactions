<?php

namespace Xekvern\Core\Player\Combat\Boss\Task;

use Xekvern\Core\Nexus;
use JsonException;
use libs\muqsit\arithmexp\Util;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Player\Combat\Boss\ArtificialIntelligence;
use Xekvern\Core\Player\Combat\Boss\Types\Alien;
use Xekvern\Core\Player\Combat\Boss\Types\CorruptedKing;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Utils\Utils;

class SpawnBossTask extends Task {

    /** @var string */
    protected string $prefix = "§l§8(§3!§8)§r §7";
    /** @var int */
    protected int $time = 1800; // 2 Hours
    /** @var bool */
    protected bool $sentWarning = false;

    /**
     * @return void
     * @throws JsonException
     */
    public function onRun(): void {
        if (!$this->sentWarning and $this->check()) {
            //Server::getInstance()->broadcastMessage(Translation::AQUA .  "The " . TextFormat::BOLD . TextFormat::YELLOW . "Corrupted King" . TextFormat::RESET. TextFormat::GRAY . " arrival has been paused until the current one is killed!");
            $this->sentWarning = true;
            $world = Nexus::getInstance()->getServer()->getWorldManager()->getWorldByName("bossarena");
            foreach($world->getPlayers() as $player) {
                /** @var NexusPlayer $player */
                $text = $player->getFloatingText("BossArena");
                if($text !== null) {
                    $bossText2 = implode(TextFormat::RESET . "\n", [
                        TextFormat::BOLD . TextFormat::RED . "BOSS",
                        TextFormat::YELLOW . "Ongoing boss...",
                    ]);
                    $text->update($bossText2);
                    $text->sendChangesTo($player);
                }
            }
            return;
        }
        if ($this->check()) {
            return;
        }
        if ($this->sentWarning) {
            $this->sentWarning = false;
        }
        $world = Nexus::getInstance()->getServer()->getWorldManager()->getWorldByName("bossarena");
        foreach($world->getPlayers() as $player) {
            /** @var NexusPlayer $player */
            if($player->getFloatingText("BossArena") === null) {
                $bossText = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::RED . "BOSS",
                    TextFormat::WHITE . "Next Arrival: " . TextFormat::YELLOW . Utils::secondsToCD($this->time),
                ]);
                $player->addFloatingText(new Position(232.5434, 70.0022, 278.509, $world), "BossArena", $bossText);
            }
            $text = $player->getFloatingText("BossArena");
            if(!$this->sentWarning) {
                $bossText1 = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::RED . "BOSS",
                    TextFormat::WHITE . "Next Arrival: " . TextFormat::YELLOW . Utils::secondsToCD($this->time),
                ]);
                $text->update($bossText1);
                $text->sendChangesTo($player);
            } elseif ($this->check()) {
                $bossText2 = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::RED . "BOSS",
                    TextFormat::YELLOW . "Ongoing boss...",
                ]);
                $text->update($bossText2);
                $text->sendChangesTo($player);
            }
        }
        // 7200, 3600, 2400, 1200, 600, 300, 30, 10, 5, 1800, 180,
        if (in_array($this->time, [60, 30])) {
            $time = $this->time . " seconds";
            if ($this->time >= 60) {
                $time = floor(($this->time / 60) % 60) . " minutes";
            }
            $message = implode(TextFormat::RESET . "\n", [
                Utils::centerAlignText(TextFormat::BOLD . TextFormat::RED . "BOSS EVENT", 58),
                Utils::centerAlignText(TextFormat::WHITE . "The Arrival of " . TextFormat::BOLD . TextFormat::YELLOW . "Corrupted King" . TextFormat::RESET . TextFormat::WHITE . " will commence in:", 58),
                Utils::centerAlignText(TextFormat::DARK_AQUA . Utils::secondsToTime($this->time), 58),
            ]);
        }
        if ($this->time <= 0) {
            if (!$this->check()) $this->summon();
            $this->time = 900;
        } else {
            $this->time--;
        }
    }

    /**
     * @return bool
     */
    public function check(): bool {
        $lvl = Server::getInstance()->getWorldManager()->getWorldByName("bossarena");
        foreach ($lvl->getEntities() as $entity) {
            if (($entity instanceof Alien) or ($entity instanceof CorruptedKing)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function summon(): void {
        $x = 232;
        $y = 70;
        $z = 278;
        $lvl = Server::getInstance()->getWorldManager()->getWorldByName("bossarena");
        $lvl->loadChunk($x, $z);
        $location = new Location($x, $y, $z, $lvl, 0, 0);
        Nexus::getInstance()->getPlayerManager()->getCombatHandler()->spawnBoss("CorruptedKing", $location); 
    }
}
