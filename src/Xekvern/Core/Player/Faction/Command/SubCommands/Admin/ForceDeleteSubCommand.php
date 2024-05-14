<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Admin;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Faction\FactionException;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use Xekvern\Core\Player\NexusPlayer;

class ForceDeleteSubCommand extends SubCommand
{

    /**
     * ForceDeleteSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("forcedelete", "/faction forcedelete <faction>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws FactionException
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $faction = $this->getCore()->getPlayerManager()->getFactionHandler()->getFaction($args[1]);
        if ($faction === null) {
            $sender->sendMessage(Translation::getMessage("invalidFaction"));
            return;
        }
        $faction->disband();
        $sender->sendMessage(Translation::getMessage("forceDelete"));
    }
}