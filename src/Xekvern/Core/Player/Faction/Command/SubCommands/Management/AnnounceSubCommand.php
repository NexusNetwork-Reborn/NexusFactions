<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Management;

use Xekvern\Core\Command\Utils\Args\TextArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class AnnounceSubCommand extends SubCommand {

    /**
     * AnnounceSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("announce", "/faction announce <message>");
        $this->registerArgument(0, new TextArgument("message"));
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
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        if($sender->getDataSession()->getFaction() === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if($sender->getDataSession()->getFactionRole() < Faction::OFFICER) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        array_shift($args);
        $message = implode(" ", $args);
        foreach($sender->getDataSession()->getFaction()->getOnlineMembers() as $player) {
            $player->sendTitle(TextFormat::GREEN . TextFormat::BOLD . "Announcement", TextFormat::GRAY . $message, 20, 60, 20);
        }
    }
}