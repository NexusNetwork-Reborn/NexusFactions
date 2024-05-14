<?php

declare(strict_types=1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Player\Rank\Rank;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListCommand extends Command {

    /**
     * ListCommand constructor.
     */
    public function __construct() {
        parent::__construct("list", "List current online players.");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $players = [];
        $rankedPlayers = [];
        $staffs = [];
        $youtubers = [];
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($player->isLoaded() === false) {
                continue;
            }
            $identifier = $player->getDataSession()->getRank()->getIdentifier();
            if($player->isDisguise()) {
                $identifier = $player->getDisguiseRank()->getIdentifier();
            }
            if($identifier === Rank::PLAYER) {
                $players[] = $player;
                continue;
            }
            if($identifier >= Rank::SUBORDINATE and $identifier <= Rank::DEITY) {
                $rankedPlayers[] = $player;
                continue;
            }
            if($identifier === Rank::YOUTUBER or $identifier === Rank::FAMOUS) {
                $youtubers[] = $player;
                continue;
            }
            else {
                $staffs[] = $player;
            }
        }
        $onlinePlayers = count($this->getCore()->getServer()->getOnlinePlayers());
        if($onlinePlayers === 0) {
            $sender->sendMessage(TextFormat::YELLOW . "There is a total of " . TextFormat::AQUA . $onlinePlayers . TextFormat::YELLOW . " online player(s).");
            return;
        }
        $list = "";
        /** @var NexusPlayer $player */
        foreach($players as $player) {
            if(empty($list)) {
                if($player->isDisguise()) {
                    $list .= TextFormat::RESET . TextFormat::WHITE . $player->getDisplayName();
                    continue;
                }
                $list .= TextFormat::RESET . TextFormat::WHITE . $player->getName();
            }
            else {
                if($player->isDisguise()) {
                    $list .= ", " . TextFormat::RESET . TextFormat::WHITE . $player->getDisplayName();
                    continue;
                }
                $list .= ", " . TextFormat::RESET . TextFormat::WHITE . $player->getName();
            }
        }
        foreach($rankedPlayers as $rankedPlayer) {
            if(empty($list)) {
                if($rankedPlayer->isDisguise()) {
                    $list .= $rankedPlayer->getDisguiseRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $rankedPlayer->getDisplayName();
                    continue;
                }
                $list .= $rankedPlayer->getDataSession()->getRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $rankedPlayer->getName();
            }
            else {
                if($rankedPlayer->isDisguise()) {
                    $list .= ", " . $rankedPlayer->getDisguiseRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $rankedPlayer->getDisplayName();
                    continue;
                }
                $list .= ", " . $rankedPlayer->getDataSession()->getRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $rankedPlayer->getName();
            }
        }
        $playerCount = count($players) + count($rankedPlayers);
        $times = (int)round(($playerCount / $onlinePlayers) * 20);
        $sender->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::YELLOW . str_repeat("|", $times) . TextFormat::GRAY . str_repeat("|", 20 - $times) . TextFormat::DARK_GRAY . "] " . Translation::getMessage("listMessage", [
                "group" => TextFormat::YELLOW . "Players:",
                "count" => TextFormat::DARK_GRAY . "(" . TextFormat::BOLD . TextFormat::YELLOW . $playerCount . TextFormat::RESET . TextFormat::DARK_GRAY . ")",
                "list" => $list
            ]));
        $list = "";
        foreach($youtubers as $youtuber) {
            if(empty($list)) {
                $list .= $youtuber->getDataSession()->getRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $youtuber->getName();
            }
            else {
                $list .= ", " . $youtuber->getDataSession()->getRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $youtuber->getName();
            }
        }
        $times = (int)round((count($youtubers) / $onlinePlayers) * 20);
        $sender->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::WHITE . str_repeat("|", $times) . TextFormat::GRAY . str_repeat("|", 20 - $times) . TextFormat::DARK_GRAY . "] " . Translation::getMessage("listMessage", [
                "group" => TextFormat::WHITE . "You" . TextFormat::RED . "Tubers",
                "count" => TextFormat::DARK_GRAY . "(" . TextFormat::BOLD . TextFormat::RED . count($youtubers) . TextFormat::RESET . TextFormat::DARK_GRAY . ")",
                "list" => TextFormat::WHITE . $list
            ]));
        $list = "";
        foreach($staffs as $staff) {
            if(empty($list)) {
                $list .= $staff->getDataSession()->getRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $staff->getName();
            }
            else {
                $list .= ", " . $staff->getDataSession()->getRank()->getColoredName() . " " . TextFormat::RESET . TextFormat::WHITE . $staff->getName();
            }
        }
        $times = (int)round((count($staffs) / $onlinePlayers) * 20);
        $sender->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::LIGHT_PURPLE . str_repeat("|", $times) . TextFormat::GRAY . str_repeat("|", 20 - $times) . TextFormat::DARK_GRAY . "] " . Translation::getMessage("listMessage", [
                "group" => TextFormat::LIGHT_PURPLE . "Staffs",
                "count" => TextFormat::DARK_GRAY . "(" . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . count($staffs) . TextFormat::RESET . TextFormat::DARK_GRAY . ")",
                "list" => TextFormat::WHITE . $list
            ]));
        $sender->sendMessage(TextFormat::YELLOW . "There is a total of " . TextFormat::AQUA . $onlinePlayers . TextFormat::YELLOW . " online player(s).");
    }
}