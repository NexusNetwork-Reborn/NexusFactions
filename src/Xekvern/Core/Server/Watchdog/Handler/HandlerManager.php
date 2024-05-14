<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Watchdog\Handler;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Server\Watchdog\Handler\Task\ResetViolationsTask;
use Xekvern\Core\Server\Watchdog\Handler\Task\TPSCheckTask;
use Xekvern\Core\Server\Watchdog\Handler\Types\AttackHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\BreakHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\AutoClickerHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\FlyHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\InstantBreakHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\JetpackHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\NoClipHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\NukeHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\ReachHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\Hacks\SpeedHandler;
use Xekvern\Core\Server\Watchdog\Handler\Types\PearlHandler;
use Xekvern\Core\Server\Watchdog\Task\CheatLogTask;
use pocketmine\utils\TextFormat;

class HandlerManager {

    /** @var Nexus */
    private $core;

    /** @var bool */
    private $halted = false;

    /** @var ReachHandler */
    private $reachHandler;

    /** @var PearlHandler */
    private $pearlHandler;

    /** @var BreakHandler */
    private $breakHandler;

    /** @var InstantBreakHandler */
    private $instantBreakHandler;

    /** @var NukeHandler */
    private $nukeHandler;

    /** @var AttackHandler */
    private $attackHandler;

    /** @var FlyHandler */
    private $flyHandler;

    /** @var NoClipHandler */
    private $noClipHandler;

    /** @var AutoClickerHandler */
    private $autoClickerHandler;

    /** @var JetpackHandler */
    private $jetpackHandler;

    /** @var SpeedHandler */
    private $speedHandler;

    /**
     * WatchdogManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getScheduler()->scheduleRepeatingTask(new TPSCheckTask($core), 20);
        $core->getScheduler()->scheduleRepeatingTask(new ResetViolationsTask($this), 12000);
        $core->getServer()->getPluginManager()->registerEvents(new HandlerListener($core), $core);
        $this->init();
    }

    public function init(): void {
        $this->reachHandler = new ReachHandler($this->core);
        $this->pearlHandler = new PearlHandler($this->core);
        $this->breakHandler = new BreakHandler($this->core);
        $this->instantBreakHandler = new InstantBreakHandler($this->core);
        $this->nukeHandler = new NukeHandler($this->core);
        $this->attackHandler = new AttackHandler($this->core);
        $this->flyHandler = new FlyHandler($this->core);
        $this->autoClickerHandler = new AutoClickerHandler($this->core);
        $this->jetpackHandler = new JetpackHandler($this->core);
        $this->speedHandler = new SpeedHandler($this->core);
    }

    /**
     * @return ReachHandler
     */
    public function getReachHandler(): ReachHandler {
        return $this->reachHandler;
    }

    /**
     * @return PearlHandler
     */
    public function getPearlHandler(): PearlHandler {
        return $this->pearlHandler;
    }

    /**
     * @return BreakHandler
     */
    public function getBreakHandler(): BreakHandler {
       return $this->breakHandler;
    }

    /**
     * @return InstantBreakHandler
     */
    public function getInstantBreakHandler(): InstantBreakHandler {
        return $this->instantBreakHandler;
    }

    /**
     * @return NukeHandler
     */
    public function getNukeHandler(): NukeHandler {
        return $this->nukeHandler;
    }

    /**
     * @return AttackHandler
     */
    public function getAttackHandler(): AttackHandler {
        return $this->attackHandler;
    }

    /**
     * @return FlyHandler
     */
    public function getFlyHandler(): FlyHandler {
        return $this->flyHandler;
    }

    /**
     * @return AutoClickerHandler
     */
    public function getAutoClickerHandler(): AutoClickerHandler {
        return $this->autoClickerHandler;
    }

    /**
     * @return JetpackHandler
     */
    public function getJetpackHandler(): JetpackHandler {
        return $this->jetpackHandler;
    }

    /**
     * @return SpeedHandler
     */
    public function getSpeedHandler(): SpeedHandler {
        return $this->speedHandler;
    }

    /**
     * @return bool
     */
    public function isHalted(): bool {
        return $this->halted;
    }

    /**
     * @param bool $halted
     */
    public function setHalted(bool $halted): void {
        if($halted) {
            $message = TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . TextFormat::BOLD . "BOOP" . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . TextFormat::RED . "Detections have been halted due to low TPS!";
            /** @var NexusPlayer $player */
            foreach($this->core->getServer()->getOnlinePlayers() as $player) {
                if($player->isLoaded() === false) {
                    continue;
                }
                if($player->getDataSession()->getRank()->getIdentifier() < Rank::TRIAL_MODERATOR or $player->getDataSession()->getRank()->getIdentifier() > Rank::OWNER) {
                    continue;
                }
                $player->sendMessage($message);
            }
            $this->core->getServer()->getAsyncPool()->increaseSize(2);
            $this->core->getServer()->getAsyncPool()->submitTaskToWorker(new CheatLogTask("[Faction] " . date("[n/j/Y][G:i:s]", time()) . TextFormat::clean($message)), 1);
            $this->core->getLogger()->info($message);
        }
        else {
            $message = TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . TextFormat::BOLD . "BOOP" . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . TextFormat::GREEN . "Detections are back online!";
            /** @var NexusPlayer $player */
            foreach($this->core->getServer()->getOnlinePlayers() as $player) {
                if($player->isLoaded() === false) {
                    continue;
                }
                if($player->getDataSession()->getRank()->getIdentifier() < Rank::TRIAL_MODERATOR or $player->getDataSession()->getRank()->getIdentifier() > Rank::OWNER) {
                    continue;
                }
                $player->sendMessage($message);
            }
            $this->core->getServer()->getAsyncPool()->increaseSize(2);
            $this->core->getServer()->getAsyncPool()->submitTaskToWorker(new CheatLogTask("[Faction] " . date("[n/j/Y][G:i:s]", time()) . TextFormat::clean($message)), 1);
            $this->core->getLogger()->info($message);
        }
        $this->halted = $halted;
    }
}