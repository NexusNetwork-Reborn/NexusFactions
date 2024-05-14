<?php

declare(strict_types = 1);


namespace Xekvern\Core\Command\Types;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Translation\Translation;

class RankUpCommand extends Command {

    /**
     * RankUpCommand constructor.
     */
    public function __construct() {
        parent::__construct("rankup", "Rank up", "/rankup", ["ru"]);
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
        $rank = $sender->getDataSession()->getRank();
        switch($rank->getIdentifier()) {
            case Rank::PLAYER:
                $price = 1000000;
                $rankId = Rank::SUBORDINATE;
                break;
            case Rank::SUBORDINATE:
                $price = 5000000;
                $rankId = Rank::KNIGHT;
                break;
            case Rank::KNIGHT:
                $price = 20000000;
                $rankId = Rank::HOPLITE;
                break;
            case Rank::HOPLITE:
                $price = 100000000;
                $rankId = Rank::PRINCE;
                break;
            case Rank::PRINCE:
                $price = 1000000000;
                $rankId = Rank::TITAN;
                break;
            default:
                if(!$sender->hasPermission("permission.subordinate")) {
                    $price = 1000000;
                    $permission = "permission.subordinate";
                }
                elseif(!$sender->hasPermission("permission.knight")) {
                    $price = 5000000;
                    $permission = "permission.knight";
                }
                elseif(!$sender->hasPermission("permission.hoplite")) {
                    $price = 20000000;
                    $permission = "permission.hoplite";
                }
                elseif(!$sender->hasPermission("permission.prince")) {
                    $price = 100000000;
                    $permission = "permission.prince";
                }
                elseif(!$sender->hasPermission("permission.titan")) {
                    $price = 1000000000;
                    $permission = "permission.titan";
                }
                else {
                    $price = null;
                    $permission = null;
                }
                break;
        }
        if((!isset($price)) or $price === null) {
            $sender->sendMessage(Translation::getMessage("maxRank"));
            return;
        }
        if($price > $sender->getDataSession()->getBalance()) {
            $sender->sendMessage(Translation::getMessage("notEnoughMoneyRankUp", [
                "amount" => TextFormat::RED . "$" . number_format($price)
            ]));
            return;
        }
        if(isset($rankId)) {
            $sender->getDataSession()->subtractFromBalance($price);
            $rank = $sender->getCore()->getPlayerManager()->getRankHandler()->getRankByIdentifier($rankId);
            $sender->getDataSession()->setRank($rank);
            $sender->getDataSession()->addPermission("permission." . strtolower($rank->getName()));
            $this->getCore()->getServer()->broadcastMessage(Translation::getMessage("rankUp", [
                "name" => TextFormat::AQUA . $sender->getName(),
                "rank" => TextFormat::YELLOW . $rank->getName()
            ]));
        }
        elseif(isset($permission)) {
            $sender->getDataSession()->subtractFromBalance($price);
            $sender->getDataSession()->addPermission((string)$permission);
            $rank = ucfirst(explode(".", $permission)[1]);
            $this->getCore()->getServer()->broadcastMessage(Translation::getMessage("rankUp", [
                "name" => TextFormat::AQUA . $sender->getName(),
                "rank" => TextFormat::YELLOW . $rank
            ]));
        }
        else {
            $sender->sendMessage(Translation::getMessage("errorOccurred"));
        }
    }
}