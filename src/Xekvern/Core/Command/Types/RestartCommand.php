<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Rank\Rank;

class RestartCommand extends Command {

    /**
     * RestartCommand constructor.
     */
    public function __construct() {
        parent::__construct("restart", "Manage restart", "/restart <queue | reset>", ["restart"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            if(!isset($args[0])) {
                $sender->sendMessage(Translation::ORANGE . "Usage: /restart (queue/reset)");
                return;
            }
            switch($args[0]) {
                case "queue":
                    $time = 30;
                    if(isset($args[1]) and is_numeric($args[1])) {
                        $time = intval($args[1]);
                    }
                    $this->getCore()->getServerManager()->getAnnouncementHandler()->getRestarter()->setRestartProgress($time);
                    $sender->sendMessage(Translation::GREEN . "You have queued a restart for the server in $time seconds...");
                    foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
                        if($player instanceof NexusPlayer){
                            if(in_array($player->getDataSession()->getRank()->getIdentifier(), [Rank::MANAGER, Rank::OWNER])) {
                                $player->sendMessage(TextFormat::BOLD . TextFormat::AQUA . $sender->getName() . " has queued the server for a restart in $time seconds...");
                            }
                            $player->sendMessage("§bThe server will restart in $time seconds by the administrator.");
                        }
                    }
                    break;
                case "reset":
                    $this->getCore()->getServerManager()->getAnnouncementHandler()->getRestarter()->setRestartProgress(10800);
                    $sender->sendMessage(Translation::RED . "You have reset the restart timer...");
                    break;
            }
        }
        return;
    }
}