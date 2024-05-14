<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Roles;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DemoteSubCommand extends SubCommand {

    /**
     * DemoteSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("demote", "/faction demote <player>");
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
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if($sender->getDataSession()->getFaction() === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerExact($args[1]);
        if(!$player instanceof NexusPlayer) {
            $name = $args[1];
            $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT faction, factionRole FROM stats WHERE username = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->bind_result($faction, $factionRole);
            $stmt->fetch();
            $stmt->close();
            if($faction === null and $factionRole === null) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
        }
        else {
            if($player->getName() === $sender->getName()) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
            $faction = $player->getDataSession()->getFaction()->getName();
            $factionRole = $player->getDataSession()->getFactionRole();
            $name = $player->getName();
        }
        if($faction !== $sender->getDataSession()->getFaction()->getName()) {
            $sender->sendMessage(Translation::getMessage("notFactionMember", [
                "name" => TextFormat::RED . $name
            ]));
            return;
        }
        if($factionRole >= $sender->getDataSession()->getFactionRole() or $factionRole === Faction::RECRUIT) {
            $sender->sendMessage(Translation::getMessage("cannotDemote", [
                "name" => TextFormat::RED . $name
            ]));
            return;
        }
        if(!$player instanceof NexusPlayer) {
            $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("UPDATE stats SET factionRole = factionRole - 1 WHERE username = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->close();
        }
        else {
            $player->getDataSession()->setFactionRole($player->getDataSession()->getFactionRole() - 1);
        }
        foreach($sender->getDataSession()->getFaction()->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::getMessage("demoted", [
                "name" => TextFormat::GREEN . $name,
                "sender" => TextFormat::LIGHT_PURPLE . $sender->getName()
            ]));
        }
    }
}