<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Roles;

use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PromoteSubCommand extends SubCommand {

    /**
     * PromoteSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("promote", "/faction promote <player>");
        $this->registerArgument(0, new TargetArgument("player"));
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
        if($sender->getDataSession()->getFactionRole() !== Faction::LEADER) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
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
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        if($player->getName() === $sender->getName()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!$sender->getDataSession()->getFaction()->isInFaction($player->getName())) {
            $sender->sendMessage(Translation::getMessage("notFactionMember", [
                "name" => TextFormat::RED . $player->getName()
            ]));
            return;
        }
        if($player->getDataSession()->getFactionRole() === Faction::OFFICER) {
            $sender->sendMessage(Translation::getMessage("cannotPromote", [
                "name" => TextFormat::RED . $player->getName()
            ]));
            return;
        }
        foreach($sender->getDataSession()->getFaction()->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::getMessage("promoted", [
                "name" => TextFormat::GREEN . $player->getName(),
                "sender" => TextFormat::LIGHT_PURPLE . $sender->getName()
            ]));
        }
        $player->getDataSession()->setFactionRole($player->getDataSession()->getFactionRole() + 1);
    }
}