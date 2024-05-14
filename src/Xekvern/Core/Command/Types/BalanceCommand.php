<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BalanceCommand extends Command {

    /**
     * BalanceCommand constructor.
     */
    public function __construct() {
        parent::__construct("balance", "Show your or another player's balance.", "/balance [player:target]", ["bal", "mymoney", "seemoney"]);
        $this->registerArgument(0, new TargetArgument("player", true));
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
        if(!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $name = "Your";
        $balance = $sender->getDataSession()->getBalance();
        if(isset($args[0])) {
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
                $name = "$args[0]'s";
            }
            else {
                $name = $player->getName() . "'s";
                $balance = $player->getDataSession()->getBalance();
            }
        }
        $sender->sendMessage(Translation::getMessage("balance", [
            "name" => $name,
            "amount" => TextFormat::GREEN . "$" . number_format((int)$balance)
        ]));
    }
}