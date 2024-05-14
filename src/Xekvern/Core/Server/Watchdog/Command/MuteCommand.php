<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Watchdog\Command;

use libs\muqsit\arithmexp\Util;
use Xekvern\Core\Command\Utils\Args\RawStringArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Args\TextArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Server\Watchdog\PunishmentEntry;
use Xekvern\Core\Server\Watchdog\WatchdogException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Utils\Utils;

class MuteCommand extends Command {

    /**
     * MuteCommand constructor.
     */
    public function __construct() {
        parent::__construct("mute", "Temporarily mute a player", "/mute <player:target> <seconds:int> <reason:string>", ["smute"]);
        $this->registerArgument(0, new TargetArgument("player"));
        $this->registerArgument(1, new RawStringArgument("time"));
        $this->registerArgument(2, new TextArgument("reason"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     * @throws WatchdogException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            if(!$sender->hasPermission("permission.staff")) {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
        }
        if(!isset($args[2])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $name = array_shift($args);
        if($this->getCore()->getServerManager()->getWatchdogHandler()->isMuted($name)) {
            $sender->sendMessage(Translation::getMessage("alreadyMuted", [
                "name" => TextFormat::YELLOW . $name,
            ]));
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerByPrefix($name);
        if($player === null) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        $seconds = array_shift($args);
        if((!is_numeric($seconds))) {
            $seconds = Utils::stringToTime($seconds);
            if($seconds === null) {
                $sender->sendMessage(Translation::getMessage("invalidAmount"));
                return;
            }
        }
        $seconds = (int)$seconds;
        $reason = implode(" ", $args);
        if(strlen($reason) > 100) {
            $sender->sendMessage(Translation::getMessage("reasonTooLong"));
            return;
        }
        $timeString = Utils::secondsToTime($seconds);
        if($commandLabel !== "smute") {
            $this->getCore()->getServer()->broadcastMessage(Translation::getMessage("muteBroadcast", [
                "name" => TextFormat::RED . $name,
                "effector" => TextFormat::DARK_RED . $sender->getName(),
                "reason" => TextFormat::YELLOW . "\"$reason\"",
                "time" => TextFormat::RED . $timeString
            ]));
        }
        $this->getCore()->getServerManager()->getWatchdogHandler()->punish($name, PunishmentEntry::MUTE, $sender->getName(), $reason, $seconds);
    }
}