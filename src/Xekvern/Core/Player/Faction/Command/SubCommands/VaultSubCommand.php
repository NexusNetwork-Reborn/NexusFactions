<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class VaultSubCommand extends SubCommand
{

    /**
     * WithdrawSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("vault", "/faction vault");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $senderFaction = $sender->getDataSession()->getFaction();
        if ($senderFaction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if (!$senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_USE_VAULT)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $senderFaction->sendVault($sender);
    }
}