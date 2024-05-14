<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Homes;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;

class SetHomeSubCommand extends SubCommand
{

    /**
     * SetHomeSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("sethome", "/faction sethome");
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
        $manager = $this->getCore()->getPlayerManager()->getFactionHandler();
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if ($sender->getDataSession()->getFaction() === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if ($sender->getDataSession()->getFactionRole() !== Faction::LEADER) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $claim = $manager->getClaimInPosition($sender->getPosition());
        if ($claim === null or $claim->getFaction()->getName() !== $sender->getDataSession()->getFaction()->getName()) {
            $sender->sendMessage(Translation::getMessage("mustBeInClaim"));
            return;
        }
        $sender->getDataSession()->getFaction()->setHome($sender->getPosition());
        $sender->sendMessage(Translation::getMessage("homeSet"));
    }
}