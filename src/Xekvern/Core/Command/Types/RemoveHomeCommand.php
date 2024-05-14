<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;

class RemoveHomeCommand extends Command {

    /**
     * RemoveHomeCommand constructor.
     */
    public function __construct() {
        parent::__construct("removehome", "Delete a home", "/removehome <name: string>", ["rmhome", "delhome"]);
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
            if(isset($args[0])) {
                $home = $sender->getDataSession()->getHome($args[0]);
                if($home === null) {
                    $sender->sendMessage(Translation::getMessage("invalidHome"));
                    return;
                }
                $sender->getDataSession()->deleteHome($args[0]);
                $sender->sendMessage(Translation::getMessage("deleteHome"));
                return;
            }
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}