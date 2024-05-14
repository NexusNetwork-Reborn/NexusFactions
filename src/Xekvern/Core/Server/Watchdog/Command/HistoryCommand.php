<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Watchdog\Command;

use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Args\TextArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Server\Watchdog\Forms\PunishHistoryForm;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;

class HistoryCommand extends Command {

    /**
     * HistoryCommand constructor.
     */
    public function __construct() {
        parent::__construct("history", "Check the history of a player", "/history <player: target>");
        $this->registerArgument(0, new TargetArgument("player"));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            if(!$sender->hasPermission("permission.staff")) {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
        }
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $player = $args[0];
        $entries = Nexus::getInstance()->getServerManager()->getWatchdogHandler()->getHistoryOf($player);
        $sender->sendForm(new PunishHistoryForm($entries));
    }
}