<?php

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Command\Forms\CustomScoreboardOptions;

class ScoreboardCommand extends Command {

    /**
     * ScoreboardCommand constructor.
     */
    public function __construct() {
        parent::__construct("scoreboard", "Customize your scoreboard");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) and (!$sender->hasPermission("permission.customscoreboard")))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->sendForm(new CustomScoreboardOptions($sender));
    }
}