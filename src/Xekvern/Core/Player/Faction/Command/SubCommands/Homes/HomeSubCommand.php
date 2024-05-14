<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Homes;

use Xekvern\Core\Command\Task\TeleportTask;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class HomeSubCommand extends SubCommand
{

    /**
     * HomeSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("home", "/faction home");
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
        if (!$senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_ACCESS_HOME)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if ($sender->getDataSession()->getFaction()->getHome() === null) {
            $sender->sendMessage(Translation::getMessage("homeNotSet"));
            return;
        }
        if ($sender->isTeleporting()) {
            $sender->sendMessage(Translation::getMessage("alreadyTeleporting", [
                "name" => "You are"
            ]));
            return;
        }
        $this->getCore()->getScheduler()->scheduleRepeatingTask(new TeleportTask($sender, $sender->getDataSession()->getFaction()->getHome(), 5), 20);
    }
}