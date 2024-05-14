<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Crate\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class GiveKeysCommand extends Command {

    /**
     * GiveKeysCommand constructor.
     */
    public function __construct() {
        parent::__construct("givekeys", "Give crate keys to a player.", "/givekeys <player:target> <crate: string> [amount = 1]");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            if(!isset($args[2])) {
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                return;
            }
            $player = $this->getCore()->getServer()->getPlayerByPrefix($args[0]);
            if(!$player instanceof NexusPlayer) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
            $crate = $this->getCore()->getServerManager()->getCrateHandler()->getCrate($args[1]);
            if($crate === null) {
                $sender->sendMessage(Translation::getMessage("invalidCrate"));
                return;
            }
            $amount = max(1, is_numeric($args[2]) ? (int)$args[2] : 1);
            $player->getDataSession()->addKeys($crate, $amount);
            $player->sendMessage(Translation::getMessage("addKeys", [
                "amount" => TextFormat::BLUE . "x$amount",
                "type" => TextFormat::YELLOW . $crate->getName()
            ]));
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
        return;
    }
}