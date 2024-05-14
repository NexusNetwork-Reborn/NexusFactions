<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PVPCommand extends Command {

    /**
     * PVPCommand constructor.
     */
    public function __construct() {
        parent::__construct("pvp", "Teleport to pvp arena.", "/pvp");
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
            $level = $sender->getServer()->getWorldManager()->getWorldByName("warzone");
            if(isset($args[0]) and $args[0] === "boss") {
                $sender->sendMessage(TextFormat::RED . " Do you mean /boss?");
                return;
            }
            $spawn = $level->getSpawnLocation();
            $this->getCore()->getScheduler()->scheduleRepeatingTask(new TeleportTask($sender, $spawn, 5), 20);
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}