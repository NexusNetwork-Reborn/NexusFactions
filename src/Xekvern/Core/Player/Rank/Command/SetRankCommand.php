<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Rank\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class SetRankCommand extends Command {

    /**
     * SetRankCommand constructor.
     */
    public function __construct() {
        parent::__construct("setrank", "Set a player's rank.", "/setrank <player:target> <group: string>", ["setgroup"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(($sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) or ($sender->hasPermission("permission.setrank"))) {
            if(!isset($args[1])) {
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                return;
            }
            $player = $this->getCore()->getServer()->getPlayerByPrefix($args[0]);
            if(!$player instanceof NexusPlayer) {
                $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("SELECT rankId FROM users WHERE username = ?");
                $stmt->bind_param("s", $args[0]);
                $stmt->execute();
                $stmt->bind_result($rankId);
                $stmt->fetch();
                $stmt->close();
                if($rankId === null) {
                    $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                    return;
                }
                return;
            }
            if($player->isLoaded() === false) {
                $sender->sendMessage(Translation::getMessage("errorOccurred"));
                return;
            }
            $rank = $this->getCore()->getPlayerManager()->getRankHandler()->getRankByName($args[1]);
            if(!$rank instanceof Rank) {
                $sender->sendMessage(Translation::getMessage("invalidRank"));
                $sender->sendMessage(TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "RANKS:");
                $sender->sendMessage(TextFormat::WHITE . implode(", ", $this->getCore()->getPlayerManager()->getRankHandler()->getRanks()));
                return;
            }
            if(isset($rankId)) {
                $id = $rank->getIdentifier();
                $stmt = $this->getCore()->getMySQLProvider()->getDatabase()->prepare("UPDATE users SET rankId = ? WHERE username = ?");
                $stmt->bind_param("is", $id, $args[0]);
                $stmt->execute();
                $stmt->close();
            }
            else {
                $player->getDataSession()->setRank($rank);
                $player->sendMessage(Translation::getMessage("setRank", [
                    "rank" => $rank->getColoredName()
                ]));
            }
            $sender->sendMessage(Translation::getMessage("rankSet", [
                "rank" => $rank->getColoredName(),
                "name" => TextFormat::GOLD . $player instanceof NexusPlayer ? $player->getName() : $args[0]
            ]));
            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
    }
}