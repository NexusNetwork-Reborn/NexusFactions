<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Management;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\Faction\FactionException;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\Faction\Command\Forms\DisbandMenuForm;

class DisbandSubCommand extends SubCommand
{

    /**
     * DisbandSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("disband", "/faction disband");
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
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $faction = $sender->getDataSession()->getFaction();
        if ($faction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if ($sender->getDataSession()->getFactionRole() !== Faction::LEADER) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->sendForm(new DisbandMenuForm());
    }
}