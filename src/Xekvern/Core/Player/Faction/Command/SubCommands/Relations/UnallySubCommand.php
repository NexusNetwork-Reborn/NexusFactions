<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Relations;

use Xekvern\Core\Command\Utils\Args\RawStringArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class UnallySubCommand extends SubCommand {

    /**
     * UnallySubCommand constructor.
     */
    public function __construct() {
        parent::__construct("unally", "/faction unally <faction>");
        $this->registerArgument(0, new RawStringArgument("faction"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $senderFaction = $sender->getDataSession()->getFaction();
        if($senderFaction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if(!$senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_ALLY)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $faction = $this->getCore()->getPlayerManager()->getFactionHandler()->getFaction($args[1]);
        if($faction === null or (!$senderFaction->isAlly($faction))) {
            $sender->sendMessage(Translation::getMessage("invalidFaction"));
            return;
        }
        foreach($senderFaction->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::getMessage("unally", [
                "faction" => TextFormat::GREEN . $faction->getName()
            ]));
        }
        foreach($faction->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::getMessage("unally", [
                "faction" => TextFormat::GREEN . $sender->getDataSession()->getFaction()->getName()
            ]));
        }
        $faction->removeAlly($senderFaction);
        $senderFaction->removeAlly($faction);
    }
}