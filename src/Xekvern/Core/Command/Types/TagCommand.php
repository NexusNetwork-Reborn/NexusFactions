<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Forms\TagListForm;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class TagCommand extends Command {

    /**
     * TagCommand constructor.
     */
    public function __construct() {
        parent::__construct("tag", "Manage tags.", "/tag <set|add>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(isset($args[0])) {
            switch($args[0]) {
                case "set":
                    if($sender instanceof NexusPlayer) {
                        $sender->sendForm(new TagListForm($sender));
                        return;
                    }
                    $sender->sendMessage(Translation::getMessage("noPermission"));
                    break;
                case "add":
                    if((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) and $sender instanceof NexusPlayer) {
                        $sender->sendMessage(Translation::getMessage("noPermission"));
                        return;
                    }
                    if(isset($args[2])) {
                        $tag = $args[2];
                        $player = $this->getCore()->getServer()->getPlayerExact($args[1]);
                        if($player instanceof NexusPlayer) {
                            $name = $player->getName();
                            $player->getDataSession()->addTag($tag);
                            $sender->sendMessage(Translation::getMessage("tagAddSuccess", [
                                "tag" => $tag,
                                "name" => TextFormat::GOLD . $name
                            ]));
                            return;
                        }
                        $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                        return;
                    }
                    $sender->sendMessage(Translation::getMessage("usageMessage", [
                        "usage" => "/tag add <player> <tag>"
                    ]));
                    return;
                    break;
                default:
                    $sender->sendMessage(Translation::getMessage("usageMessage", [
                        "usage" => $this->getUsage()
                    ]));
                    return;
            }
        }
        $sender->sendMessage(Translation::getMessage("usageMessage", [
            "usage" => $this->getUsage()
        ]));
        return;
    }
}