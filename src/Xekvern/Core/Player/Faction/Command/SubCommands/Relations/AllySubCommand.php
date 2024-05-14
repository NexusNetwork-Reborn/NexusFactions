<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Relations;

use Xekvern\Core\Command\Utils\Args\RawStringArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\Faction\PermissionManager;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class AllySubCommand extends SubCommand {

    /**
     * AllySubCommand constructor.
     */
    public function __construct() {
        parent::__construct("ally", "/faction ally <faction>");
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
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
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
        $faction = $this->getCore()->getPlayerManager()->getFactionHandler()->getFaction($args[1]);
        if($faction === null or $senderFaction->getName() === $faction->getName()) {
            $sender->sendMessage(Translation::getMessage("invalidFaction"));
            return;
        }
        if(count($faction->getAllies()) >= Faction::MAX_ALLIES) {
            $sender->sendMessage(Translation::getMessage("factionMaxAllies", [
                "faction" => TextFormat::RED . $faction->getName()
            ]));
            return;
        }
        if($faction->isAllying($senderFaction)) {
            $senderFaction->addAlly($faction);
            $faction->addAlly($senderFaction);
            foreach($faction->getOnlineMembers() as $member) {
                $member->sendMessage(Translation::getMessage("allyAdd", [
                    "faction" => TextFormat::LIGHT_PURPLE . $senderFaction->getName()
                ]));
            }
            foreach($senderFaction->getOnlineMembers() as $member) {
                $member->sendMessage(Translation::getMessage("allyAdd", [
                    "faction" => TextFormat::LIGHT_PURPLE . $faction->getName()
                ]));
            }
        }
        else {
            $senderFaction->addAllyRequest($faction);
            foreach($faction->getOnlineMembers() as $member) {
                $member->sendMessage(Translation::getMessage("allyRequest", [
                    "senderFaction" => TextFormat::GREEN . $senderFaction->getName(),
                    "faction" => TextFormat::LIGHT_PURPLE . $faction->getName()
                ]));
            }
            foreach($senderFaction->getOnlineMembers() as $member) {
                $member->sendMessage(Translation::getMessage("allyRequest", [
                    "senderFaction" => TextFormat::GREEN . $senderFaction->getName(),
                    "faction" => TextFormat::LIGHT_PURPLE . $faction->getName()
                ]));
            }
        }
    }
}