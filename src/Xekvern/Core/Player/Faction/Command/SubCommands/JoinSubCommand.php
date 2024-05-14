<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use Xekvern\Core\Command\Utils\Args\RawStringArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class JoinSubCommand extends SubCommand
{

    /**
     * JoinSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("join", "/faction join <faction>");
        $this->registerArgument(0, new RawStringArgument("faction"));
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
        if (!$faction->isInvited($sender)) {
            $sender->sendMessage(Translation::getMessage("notInvited", [
                "faction" => TextFormat::RED . $faction->getName()
            ]));
            return;
        }
        if (count($faction->getMembers()) >= Faction::MAX_MEMBERS) {
            $sender->sendMessage(Translation::getMessage("factionMaxMembers", [
                "faction" => TextFormat::RED . $faction->getName()
            ]));
            return;
        }
        if ($sender->getDataSession()->getFaction() !== null) {
            $sender->sendMessage(Translation::getMessage("mustLeaveFaction"));
            return;
        }
        $faction->addMember($sender);
        $faction->removeInvite($sender);
        foreach ($faction->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::getMessage("factionJoin", [
                "name" => TextFormat::GREEN . $sender->getName()
            ]));
        }
    }
}