<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Boss\Command;

use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;

class BossCommand extends Command {

    /**
     * BossCommand constructor.
     */
    public function __construct() {
        parent::__construct("boss", "Teleport to boss arena.", "/boss");
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
            if (isset($args[0]) && $sender->getServer()->isOp($sender->getName())) {
                if ($args[0] == "spawn") {
                    if(isset($args[1])) {
                        $x = 232;
                        $y = 70;
                        $z = 278;
                        $lvl = Server::getInstance()->getWorldManager()->getWorldByName("bossarena");
                        $lvl->loadChunk($x, $z);
                        $location = new Location($x, $y, $z, $lvl, 0, 0);
                        if ($args[1] == "CorruptedKing") {
                            Nexus::getInstance()->getPlayerManager()->getCombatHandler()->spawnBoss("CorruptedKing", $location); 
                            $sender->sendMessage(Translation::GREEN . "Successfully summoned the boss!");
                            Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::RED . $sender->getName() . " has spawned the Corrupted King at the arena do /boss to get there!");
                        } elseif ($args[1] == "Alien") {
                            Nexus::getInstance()->getPlayerManager()->getCombatHandler()->spawnBoss("Alien", $location); 
                            $sender->sendMessage(Translation::GREEN . "Successfully summoned the boss!");
                            Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::RED . $sender->getName() . " has spawned the Alien at the arena do /boss to get there!");
                        }
                    } else {
                        $sender->sendMessage(Translation::RED . "What type of boss are you trying to spawn? The available bosses are: Alien, CorruptedKing");
                    }
                } else {
                    $sender->sendMessage(Translation::RED . "Invalid argument given.");
                }
            } else {
                $level = $sender->getServer()->getWorldManager()->getWorldByName("bossarena");
                $spawn = $level->getSpawnLocation();
                $this->getCore()->getScheduler()->scheduleRepeatingTask(new TeleportTask($sender, $spawn, 5), 20);
            }
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}