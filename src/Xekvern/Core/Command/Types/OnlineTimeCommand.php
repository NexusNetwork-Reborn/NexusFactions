<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class OnlineTimeCommand extends Command {

    /**
     * OnlineTimeCommand constructor.
     */
    public function __construct() {
        parent::__construct("onlinetime", "Manage your online time.", "/onlinetime top [page=1] or /onlinetime total <player:target>", ["ot"]);
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
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        switch($args[0]) {
            case "total":
                $name = "Your";
                $ot = $sender->getDataSession()->getOnlineTime();
                if(isset($args[1])) {
                    $player = $this->getCore()->getServer()->getPlayerExact($args[1]);
                    if(!$player instanceof NexusPlayer) {
                        $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT username, onlineTime FROM stats WHERE username = ?");
                        $stmt->bind_param("s", $args[0]);
                        $stmt->execute();
                        $stmt->bind_result($name, $ot);
                        $stmt->fetch();
                        $stmt->close();
                        if($ot === null) {
                            $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                            return;
                        }
                    }
                    else {
                        $name = $player->getName() . "'s";
                        $ot = $player->getDataSession()->getOnlineTime();
                    }
                }
                $sender->sendMessage(Translation::getMessage("onlineTime", [
                    "name" => $name,
                    "amount" => TextFormat::LIGHT_PURPLE . Utils::secondsToTime($ot)
                ]));
                break;
            case "top":
                if(isset($args[1])) {
                    if(!is_numeric($args[1])) {
                        $sender->sendMessage(Translation::getMessage("invalidAmount"));
                        return;
                    }
                    $page = (int)$args[1];
                }
                else {
                    $page = 1;
                }
                $place = (($page - 1) * 10);
                $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT username, onlineTime FROM stats ORDER BY onlineTime DESC LIMIT 10 OFFSET " . $place);
                $stmt->execute();
                $stmt->bind_result($name, $ot);
                ++$place;
                $text = $text = TextFormat::GOLD . TextFormat::BOLD . "TOP ONLINE TIMES " . TextFormat::RESET . TextFormat::GRAY . "Page $page";
                while($stmt->fetch()) {
                    $text .= "\n" . TextFormat::BOLD . TextFormat::YELLOW . "$place. " . TextFormat::RESET . TextFormat::WHITE . $name . TextFormat::AQUA . " | " . TextFormat::LIGHT_PURPLE . Utils::secondsToTime($ot);
                    $place++;
                }
                $stmt->close();
                $sender->sendMessage($text);
                break;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ])); 
                break;
        }
    }
}