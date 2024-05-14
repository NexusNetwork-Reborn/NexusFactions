<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Arguments\OnOrOffArgument;
use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class FreezeCommand extends Command {

    /**
     * FreezeCommand constructor.
     */
    public function __construct() {
        parent::__construct("freeze", "Freeze and unfreeze player.", "/freeze <on|off> <player:target>");
        $this->registerArgument(0, new OnOrOffArgument("mode"));
        $this->registerArgument(1, new TargetArgument("player"));
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
            if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
                if(!$sender->hasPermission("permission.staff")) {
                    $sender->sendMessage(Translation::getMessage("noPermission"));
                    return;
                }
            }
            switch($args[0]) {
                case "on":
                    if(!isset($args[1])) {
                        $sender->sendMessage(Translation::getMessage("usageMessage", [
                            "usage" => $this->getUsage()
                        ]));
                        return;
                    }
                    $player = $this->getCore()->getServer()->getPlayerByPrefix($args[1]);
                    if(!$player instanceof NexusPlayer) {
                        $sender->sendMessage(Translation::getMessage("invalidPlayer"));

                        return;
                    }
                    $player->setNoClientPredictions();
                    $player->setFrozen();
                    $player->sendMessage(Translation::getMessage("freezePlayer"));
                    $sender->sendMessage(Translation::getMessage("freezeSender", [
                        "player" => TextFormat::GREEN . $player->getName()
                    ]));
                    break;
                case "off":
                    if(!isset($args[1])) {
                        $sender->sendMessage(Translation::getMessage("usageMessage", [
                            "usage" => $this->getUsage()
                        ]));
                        return;
                    }
                    $player = $this->getCore()->getServer()->getPlayerByPrefix($args[1]);
                    if(!$player instanceof NexusPlayer) {
                        $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                        return;
                    }
                    $player->setNoClientPredictions(false);
                    $player->setFrozen(false);
                    $player->sendMessage(Translation::getMessage("unfreezePlayer"));
                    $sender->sendMessage(Translation::getMessage("unfreezeSender", [
                        "player" => TextFormat::GREEN . $player->getName()
                    ]));
                    break;
            }
            return;
        }
        $sender->sendMessage(Translation::getMessage("usageMessage", [
            "usage" => $this->getUsage()
        ]));
        return;
    }
}