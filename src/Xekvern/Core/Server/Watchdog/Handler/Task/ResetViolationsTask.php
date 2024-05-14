<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Task;

use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Watchdog\Handler\HandlerManager;
use Xekvern\Core\Server\Watchdog\Task\CheatLogTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Rank\Rank;

class ResetViolationsTask extends Task {

    /** @var HandlerManager */
    private $manager;

    /**
     * ResetViolationsTask constructor.
     *
     * @param HandlerManager $manager
     */
    public function __construct(HandlerManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @param int $tick
     */
    public function onRun(): void {
        $this->manager->getAutoClickerHandler()->resetViolations();
        $this->manager->getFlyHandler()->resetViolations();
        $this->manager->getInstantBreakHandler()->resetViolations();
        $this->manager->getNukeHandler()->resetViolations();
        $this->manager->getReachHandler()->resetViolations();
        $message = TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . TextFormat::BOLD . "BOOP" . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . TextFormat::YELLOW . "Violations have been reset!";
        foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            /** @var NexusPlayer $onlinePlayer */
            if($onlinePlayer->isLoaded() === false) {
                continue;
            }
            if($onlinePlayer->getDataSession()->getRank()->getIdentifier() < Rank::TRIAL_MODERATOR or $onlinePlayer->getDataSession()->getRank()->getIdentifier() > Rank::OWNER) {
                continue;
            }
            Nexus::getInstance()->getLogger()->notice("[BOOP] Violations has been reset!");
        }
        Nexus::getInstance()->getLogger()->info($message);
        Server::getInstance()->getAsyncPool()->increaseSize(2);
        Server::getInstance()->getAsyncPool()->submitTaskToWorker(new CheatLogTask("[Faction] " . date("[n/j/Y][G:i:s]", time()) . TextFormat::clean($message)), 0);
    }
}