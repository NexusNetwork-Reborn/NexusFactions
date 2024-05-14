<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Management;

use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class InviteSubCommand extends SubCommand
{

    /**
     * InviteSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("invite", "/faction invite <player>");
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
        if (!$senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_INVITE)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (count($sender->getDataSession()->getFaction()->getMembers()) >= Faction::MAX_MEMBERS) {
            $sender->sendMessage(Translation::getMessage("factionMaxMembers", [
                "faction" => TextFormat::RED . $sender->getDataSession()->getFaction()->getName()
            ]));
            return;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerByPrefix($args[1]);
        if (!$player instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        if (!$player->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        if ($player->getDataSession()->getFaction() !== null) {
            $sender->sendMessage(Translation::RED . "This player is already in a faction.");
            return;
        }
        $sender->getDataSession()->getFaction()->addInvite($player);
        $sender->sendMessage(Translation::getMessage("inviteSentSender", [
            "name" => TextFormat::GREEN . $player->getName()
        ]));
        $player->sendMessage(Translation::getMessage("inviteSentPlayer", [
            "faction" => TextFormat::GREEN . $sender->getDataSession()->getFaction()->getName()
        ]));
    }
}