<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Command\Arguments\ChatTypeArgument;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ChatSubCommand extends SubCommand {

    /**
     * ChatSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("chat", "/faction chat [mode]", ["c"]);
        $this->registerArgument(0, new ChatTypeArgument("mode", true));
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
        if(isset($args[1])) {
            switch($args[1]) {
                case "p":
                case "public":
                    $mode = NexusPlayer::PUBLIC;
                    break;
                case "a":
                case "ally":
                    $mode = NexusPlayer::ALLY;
                    break;
                case "f":
                case "faction":
                    $mode = NexusPlayer::FACTION;
                    break;
                default:
                    $mode = $sender->getChatMode() + 1;
                    break;
            }
        }
        else {
            $mode = $sender->getChatMode() + 1;
        }
        if($mode > 2) {
            $mode = 0;
        }
        $sender->setChatMode($mode);
        $sender->sendMessage(Translation::getMessage("chatModeSwitch", [
            "mode" =>  TextFormat::GREEN . strtoupper($sender->getChatModeToString())
        ]));
    }
}