<?php

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;

class TogglePrivateMessagesCommand extends Command {

    /**
     * TogglePrivateMessagesCommand constructor.
     */
    public function __construct() {
        parent::__construct("tpm", "Toggle private messages");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->togglePMs();
        $sender->sendMessage(Translation::getMessage("pmToggle"));
    }
}