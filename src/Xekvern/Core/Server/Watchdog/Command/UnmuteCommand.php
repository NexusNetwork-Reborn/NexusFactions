<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Watchdog\Command;

use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class UnmuteCommand extends Command {

    /**
     * UnmuteCommand constructor.
     */
    public function __construct() {
        parent::__construct("unmute", "Relieve a player from a mute", "/unmute <player:target>");
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
        if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            if(!$sender->hasPermission("permission.mod")) {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
        }
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $name = $args[0];
        if(!$this->getCore()->getServerManager()->getWatchdogHandler()->isMuted($name)) {
            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
            return;
        }
        $this->getCore()->getServer()->broadcastMessage(Translation::getMessage("punishmentRelivedBroadcast", [
            "name" => TextFormat::GREEN . $name,
            "effector" => TextFormat::LIGHT_PURPLE . $sender->getName(),
        ]));
        $this->getCore()->getServerManager()->getWatchdogHandler()->relieve($this->getCore()->getServerManager()->getWatchdogHandler()->getMute($name), $sender->getName());
    }
}