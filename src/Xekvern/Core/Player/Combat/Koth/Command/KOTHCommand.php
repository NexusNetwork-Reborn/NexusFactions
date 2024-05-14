<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Combat\Koth\Command;

use Xekvern\Core\Player\Combat\Koth\Task\StartKOTHGameTask;
use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\world\Position;

class KOTHCommand extends Command {

    /**
     * KOTHCommand constructor.
     */
    public function __construct() {
        parent::__construct("koth", "Start a KOTH game/Teleport to KOTH");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof NexusPlayer) {
            $kothManager = $this->getCore()->getPlayerManager()->getCombatHandler();
            if($kothManager->getKOTHGame() !== null) {
                $level = $sender->getServer()->getWorldManager()->getWorldByName("warzone");
                $this->getCore()->getScheduler()->scheduleRepeatingTask(new TeleportTask($sender, new Position(-325, 131, 287, $level), 5), 20);
                return;
            }
        }
        if($sender->getServer()->isOp($sender->getName()) or $sender instanceof ConsoleCommandSender or $sender->hasPermission("permission.admin")) {
            $kothManager = $this->getCore()->getPlayerManager()->getCombatHandler();
            if($kothManager->getKOTHGame() !== null) {
                $sender->sendMessage(Translation::getMessage("kothRunning"));
                return;
            }
            $kothManager->initiateKOTHGame();
            $this->getCore()->getScheduler()->scheduleRepeatingTask(new StartKOTHGameTask($this->getCore()), 20);
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
    }
}