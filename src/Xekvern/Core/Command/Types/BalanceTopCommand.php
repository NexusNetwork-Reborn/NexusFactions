<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;

class BalanceTopCommand extends Command {

    /**
     * BalanceTopCommand constructor.
     */
    public function __construct() {
        parent::__construct("balancetop", "Show the richest players.", "/balancetop [page = 1]", ["baltop", "topmoney"]);
        $this->registerArgument(0, new IntegerArgument("page = 1", true));
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
            if(!is_numeric($args[0])) {
                $sender->sendMessage(Translation::getMessage("invalidAmount"));
                return;
            }
            $page = (int)$args[0];
        }
        else {
            $page = 1;
        }
        $place = (($page - 1) * 10);
        if ($place < 0) {
            $place = 0;
        }
        $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT username, balance FROM stats ORDER BY balance DESC LIMIT 10 OFFSET " . $place);
        $stmt->execute();
        $stmt->bind_result($name, $balance);
        ++$place;
        $text = $text = TextFormat::GOLD . TextFormat::BOLD . "RICHEST PLAYERS " . TextFormat::RESET . TextFormat::GRAY . "Page $page";
        while($stmt->fetch()) {
            $text .= "\n" . TextFormat::BOLD . TextFormat::YELLOW . "$place. " . TextFormat::RESET . TextFormat::WHITE . $name . TextFormat::AQUA . " | " . TextFormat::LIGHT_PURPLE . "$" . number_format($balance);
            $place++;
        }
        $stmt->close();
        $sender->sendMessage($text);
    }
}