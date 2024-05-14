<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class GlobalMuteCommand extends Command {

    /**
     * GlobalMuteCommand constructor.
     */
    public function __construct() {
        parent::__construct("globalmute", "Globally mute the server");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR) or $sender instanceof ConsoleCommandSender or $sender->hasPermission("permission.admin")) {
            if($this->getCore()->isGlobalMuted()) {
                Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::GREEN . "GLOBAL MUTE HAS BEEN TOGGLED OFF! YOU MAY CHAT NOW!");
                $this->getCore()->setGlobalMute(false);
                return;
            }
            Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::RED . "GLOBAL MUTE HAS BEEN TOGGLED ON! ONLY STAFF MEMBERS CAN CHAT!");
            $this->getCore()->setGlobalMute(true);
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
    }
}