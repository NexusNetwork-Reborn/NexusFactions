<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class SetMoneyCommand extends Command {

    /**
     * SetMoneyCommand constructor.
     */
    public function __construct() {
        parent::__construct("setmoney", "Set a player's balance.", "/setmoney <player:target> <amount: int>");
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
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $player = $this->getCore()->getServer()->getPlayerByPrefix($args[0]);
        if(!$player instanceof NexusPlayer) {
            $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT balance FROM stats WHERE username = ?");
            $stmt->bind_param("s", $args[0]);
            $stmt->execute();
            $stmt->bind_result($balance);
            $stmt->fetch();
            $stmt->close();
            if($balance === null) {
                $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                return;
            }
        }
        if(!is_numeric($args[1])) {
            $sender->sendMessage(Translation::getMessage("notNumeric"));
            return;
        }
        if(isset($balance)) {
            $balance = (int)$args[1];
            $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("UPDATE stats SET balance = ? WHERE username = ?");
            $stmt->bind_param("is", $balance, $args[0]);
            $stmt->execute();
            $stmt->close();
        }
        else {
            $player->getDataSession()->setBalance((int)$args[1]);
        }
        $sender->sendMessage(Translation::getMessage("addMoneySuccess", [
            "amount" => TextFormat::GREEN . "$" . number_format((int)$args[1]),
            "name" => TextFormat::GOLD . $player ? $player->getName() : $args[0]
        ]));
    }
}
