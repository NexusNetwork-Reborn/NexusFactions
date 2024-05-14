<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\CommandManager;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class DisguiseCommand extends Command {

    /**
     * DisguiseCommand constructor.
     */
    public function __construct() {
        parent::__construct("disguise", "Remove yourself from /list", "/disguise");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) and (!$sender->hasPermission("permission.staff")))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if($sender->isDisguise()) {
            $this->getCore()->getCommandManager()->removeUsedDisguise($sender->getDisplayName());
            $sender->setDisplayName($sender->getName());
            $sender->setDisguiseRank(null);
            $sender->setDisguise(false);
            $sender->sendMessage(Translation::getMessage("disguiseOff"));
        }
        else {
            $sender->setDisguise(true);
            $disguise = $this->getCore()->getCommandManager()->selectDisguise();
            if($disguise === null) {
                $sender->sendMessage(Translation::getMessage("noNames"));
                return;
            }
            $sender->setDisplayName($disguise);
            $rank = $this->getCore()->getPlayerManager()->getRankHandler()->getRankByIdentifier(CommandManager::DISGUISES[$disguise]);
            $sender->setDisguiseRank($rank);
            $sender->sendMessage(Translation::getMessage("disguiseOn", [
                "name" => TextFormat::YELLOW . $disguise,
                "rank" => $rank->getColoredName()
            ]));
        }
    }
}