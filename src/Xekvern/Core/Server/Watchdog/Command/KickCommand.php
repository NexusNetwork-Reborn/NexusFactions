<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Watchdog\Command;

use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Args\TextArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class KickCommand extends Command {

    /**
     * KickCommand constructor.
     */
    public function __construct() {
        parent::__construct("kick", "Kick a player", "/kick <player: target> <reason: string>", ["skick"]);
        $this->registerArgument(0, new TargetArgument("player"));
        $this->registerArgument(1, new TextArgument("reason"));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            if(!$sender->hasPermission("permission.staff")) {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
        }
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerByPrefix(array_shift($args));
        if($player === null) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        $reason = implode(" ", $args);
        if($commandLabel !== "skick") {
            $this->getCore()->getServer()->broadcastMessage(Translation::getMessage("kickBroadcast", [
                "name" => TextFormat::RED . $player->getName(),
                "effector" => TextFormat::DARK_RED . $sender->getName(),
                "reason" => TextFormat::YELLOW . "\"$reason\""
            ]));
        }
        $player->close(null, Translation::getMessage("kickMessage", [
            "name" => TextFormat::RED . $sender->getName(),
            "reason" => TextFormat::YELLOW . $reason
        ]));
    }
}