<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class BroadcastCommand extends Command {

    /**
     * BroadcastCommand constructor.
     */
    public function __construct() {
        parent::__construct("broadcast", "Broadcast messages.", null, ["bc"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR) or $sender instanceof ConsoleCommandSender) {
            $message = implode(" ", $args);
            $message = str_replace("&", TextFormat::ESCAPE, $message);
            $this->getCore()->getServer()->broadcastMessage($message);
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}
