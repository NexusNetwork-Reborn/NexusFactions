<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Update;

use libs\muqsit\arithmexp\Util;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Update\Task\SaveTask;
use Xekvern\Core\Server\Update\Task\UpdateTask;
use Xekvern\Core\NexusException;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Server\Update\Task\AutoSellTask;
use Xekvern\Core\Server\Update\Task\LeaderboardsTask;
use Xekvern\Core\Server\Update\Utils\Scoreboard;
use Xekvern\Core\Utils\Utils;

class UpdateHandler {

    /** @var Nexus */
    private $core;

    /**
     * UpdateHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getScheduler()->scheduleRepeatingTask(new UpdateTask($core), 1);
        $core->getScheduler()->scheduleRepeatingTask(new LeaderboardsTask($core), 20);
        $core->getScheduler()->scheduleRepeatingTask(new AutoSellTask($core), 1);
        $core->getScheduler()->scheduleRepeatingTask(new SaveTask($core), 12000);
    }

    /**
     * @param NexusPlayer $player
     *
     * @throws NexusException
     */
    public function updateScoreboard(NexusPlayer $player): void {
        $scoreboard = $player->getScoreboard();
        if($scoreboard === null or (!$player->isLoaded())) {
            return;
        }
        if($scoreboard->isSpawned() === false) {
            if($player->isLoaded()) {
                $player->initializeScoreboard();
            }
            else {
                return;
            }
        }
        if($player->isUsingFMapHUD() === true) {
            return;
        }
        if($player->isTagged() === true) {
            $this->updateCombatHUD($scoreboard, $player);
            return;
        }
        $this->updateRegularHUD($scoreboard, $player);
    }

    /**
     * @param Scoreboard $scoreboard
     * @param NexusPlayer $player
     *
     * @throws NexusException
     */
    public function updateRegularHUD(Scoreboard $scoreboard, NexusPlayer $player): void {
        $scoreboard->setScoreLine(1, " ");
        $scoreboard->setScoreLine(2, " " . $player->getDataSession()->getRank()->getColoredName() . TextFormat::RESET . TextFormat::WHITE . " " . $player->getName());
        $scoreboard->setScoreLine(3, " ");
        $scoreboard->setScoreLine(4, TextFormat::BOLD . TextFormat::AQUA . " STATS");
        $scoreboard->setScoreLine(5, TextFormat::RESET . TextFormat::YELLOW . "   K/D: " . TextFormat::RESET . TextFormat::WHITE . Utils::shrinkNumber($player->getDataSession()->getKills()) . ":" . Utils::shrinkNumber($player->getDataSession()->getDeaths()));
        $scoreboard->setScoreLine(6, TextFormat::RESET . TextFormat::YELLOW . "   Balance: " . TextFormat::RESET . TextFormat::WHITE . "$" . Utils::shrinkNumber($player->getDataSession()->getBalance()));
        $scoreboard->setScoreLine(7, TextFormat::RESET . TextFormat::YELLOW . "   Power: " . TextFormat::RESET . TextFormat::WHITE . Utils::shrinkNumber($player->getDataSession()->getPower()));
        $scoreboard->setScoreLine(8, TextFormat::RESET . TextFormat::YELLOW . "   Lucky Blocks: " . TextFormat::RESET . TextFormat::WHITE . Utils::shrinkNumber($player->getDataSession()->getLuckyBlocksMined()));
        $scoreboard->setScoreLine(9, " ");
        $scoreboard->setScoreLine(10, TextFormat::RESET . TextFormat::LIGHT_PURPLE . " >> store.nexuspe.net");
        $scoreboard->setScoreLine(11, TextFormat::RESET . TextFormat::LIGHT_PURPLE . " >> vote.nexuspe.net");
        if($scoreboard->getLine(12) !== null) {
            $scoreboard->removeLine(12);
        }
        if($scoreboard->getLine(13) !== null) {
            $scoreboard->removeLine(13);
        }
        if($scoreboard->getLine(14) !== null) {
            $scoreboard->removeLine(14);
        }
        if($scoreboard->getLine(15) !== null) {
            $scoreboard->removeLine(15);
        }
    }

    public function scoreboardDataFill(int $line, NexusPlayer $player): string{
        $lineString = $player->getDataSession()->getCustomScoreboardLine($line);
        $replacements = [
            '{empty}' => '',
            '{player_username}' => $player->getName(),
            '{player_rank}' => $player->getDataSession()->getRank()->getColoredName(),
            '{player_balance}' => number_format($player->getDataSession()->getBalance()),
            '{player_power}' => $player->getDataSession()->getPower()
        ];
        $lineString = strtr($lineString, $replacements);
        return $lineString;
    }

    public function scoreboardDataExist(int $line, NexusPlayer $player): bool{
        if($player->getDataSession()->getCustomScoreboardLine($line) === ""){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @param Scoreboard $scoreboard
     * @param NexusPlayer $player
     *
     * @throws NexusException
     */
    public function updateCombatHUD(Scoreboard $scoreboard, NexusPlayer $player): void {
        $scoreboard->setScoreLine(1, " ");
        $scoreboard->setScoreLine(2, " " . $player->getDataSession()->getRank()->getColoredName() . TextFormat::RESET . TextFormat::WHITE . " " . $player->getName());
        $scoreboard->setScoreLine(3, " ");
        $scoreboard->setScoreLine(4, TextFormat::RESET . " " . TextFormat::RED . " Target: " . TextFormat::RESET . TextFormat::WHITE . $player->getLastHit()); // Storing last hit @ NexusPlayer.php
        $scoreboard->setScoreLine(5, TextFormat::RESET . " " . TextFormat::RED . " Your Ping: " . TextFormat::RESET . TextFormat::WHITE . $player->getNetworkSession()->getPing() . "ms");
        $scoreboard->setScoreLine(6, TextFormat::RESET . " " . TextFormat::RED . " Combat Time: " . TextFormat::RESET . TextFormat::WHITE . $player->combatTagTime() . "s");
        $scoreboard->setScoreLine(7, " ");
        $scoreboard->setScoreLine(8, TextFormat::RESET . TextFormat::LIGHT_PURPLE . ">> store.nexuspe.net");
        $scoreboard->setScoreLine(9, TextFormat::RESET . TextFormat::LIGHT_PURPLE . ">> vote.nexuspe.net");
        if($scoreboard->getLine(10) !== null) {
            $scoreboard->removeLine(10);
        }
        if($scoreboard->getLine(11) !== null) {
            $scoreboard->removeLine(11);
        }
        if($scoreboard->getLine(12) !== null) {
            $scoreboard->removeLine(12);
        }
        if($scoreboard->getLine(13) !== null) {
            $scoreboard->removeLine(13);
        }
    }
}