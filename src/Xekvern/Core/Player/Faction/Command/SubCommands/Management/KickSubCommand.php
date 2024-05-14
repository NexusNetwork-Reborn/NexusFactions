<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Management;

use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class KickSubCommand extends SubCommand
{

    /**
     * KickSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("kick", "/faction kick <player>");
        $this->registerArgument(0, new TargetArgument("player"));
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
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerExact($args[1]) !== null ? $this->getCore()->getServer()->getPlayerExact($args[1]) : $args[1];
        if($player instanceof NexusPlayer) {
            if(!$sender->getDataSession()->getFaction()->isInFaction($player->getName())) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
            if ($player->getName() === $sender->getName()) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
            $role = $player->getDataSession()->getFactionRole();
            $name = $player->getName();
            if($role === null) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
        } else {
            $name = $args[1];
        }
        //else {
            //$stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT factionRole FROM stats WHERE username = ?");
            //$stmt->bind_param("s", $player);
            //$stmt->execute();
            //$stmt->bind_result($role);
            //$stmt->fetch();
           // $stmt->close();
            //$role = $role;
            //$name = $args[1];
            //if($role === null) {
                //$sender->sendMessage(Translation::getMessage("invalidPlayer"));
                //return;
            //}
        //}
        //if($role !== null and $sender->getDataSession()->getFactionRole() <= $role) {
            //$sender->sendMessage(Translation::getMessage("noPermission"));
            //return;
        //}
        foreach($sender->getDataSession()->getFaction()->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::getMessage("factionLeave", [
                "name" => TextFormat::GREEN . $name
            ]));
        }
        $sender->getDataSession()->getFaction()->removeMember($name);
    }
}