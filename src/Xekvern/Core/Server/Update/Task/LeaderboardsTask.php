<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Update\Task;

use libs\utils\FloatingTextParticle;
use libs\utils\UtilsException;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;

class LeaderboardsTask extends Task {

    /** @var Nexus */
    private $core;

    /** @var NexusPlayer[] */
    private $players = [];

    /** @var FloatingTextParticle[] */
    private $floatingTexts = [];

    /**
     * LeaderboardsTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param int $tick
     *
     * @throws UtilsException
     */
    public function onRun(): void {
        $level = $this->core->getServer()->getWorldManager()->getDefaultWorld();
        if(empty($this->players)) {
            $this->players = $level->getPlayers();
            $this->floatingTexts = [];
            if(!empty($this->players)) {
                $this->setFloatingTexts();
            }
        }
        if(empty($this->floatingTexts)) {
            return;
        }
        $player = $this->selectPlayer();
        if($player === null) {
            return;
        }
        foreach($this->floatingTexts as $id => $data) {
            $text = $player->getFloatingText($id);
            if($text === null) {
                $player->addFloatingText($data[0], $id, $data[1]);
                continue;
            }
            $text->update($data[1]);
            $text->sendChangesTo($player);
        }
    }

    /**
     * @return NexusPlayer|null
     */
    public function selectPlayer(): ?NexusPlayer {
        $level = $this->core->getServer()->getWorldManager()->getDefaultWorld();
        if(empty($this->players)) {
            return null;
        }
        $player = array_shift($this->players);
        if(!$player instanceof NexusPlayer) {
            return $this->selectPlayer();
        }
        if($player->isOnline() === false or $player->getPosition()->getWorld()->getFolderName() !== $level->getFolderName()) {
            return $this->selectPlayer();
        }
        return $player;
    }

    public function setFloatingTexts(): void {
        $level = $this->core->getServer()->getWorldManager()->getDefaultWorld();
        $this->floatingTexts = [];
        $connector = $this->core->getMySQLProvider()->getConnector();
        $val = [];
        foreach(Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getFactions() as $name => $fac) {
            $val[$name] = $fac->getClaimValue();
        }
        arsort($val);
        $place = 0;
        $text = TextFormat::GOLD . TextFormat::BOLD . "TOP 10 RICHEST FACTIONS";
        foreach($val as $fac => $amount) {
            $place++;
            if($place < 11) {
                $text .= "\n" . TextFormat::BOLD . TextFormat::YELLOW . "$place. " . TextFormat::RESET . TextFormat::WHITE . $fac . TextFormat::AQUA . " | " . TextFormat::LIGHT_PURPLE . "$" . number_format((int)$amount);
            }
        }
        $this->floatingTexts["Faction Value"] = [
            new Position(57.4999, 59.1154, -284.5225, $level),
            $text
        ];
        $str = [];
        foreach(Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getFactions() as $name => $fac) {
            $str[$name] = $fac->getStrength();
        }
        arsort($str);
        $place = 0;
        $text = TextFormat::GOLD . TextFormat::BOLD . "TOP 10 STRONGEST FACTIONS";
        foreach($str as $fac => $amount) {
            $place++;
            if($place < 11) {
                $text .= "\n" . TextFormat::BOLD . TextFormat::YELLOW . "$place. " . TextFormat::RESET . TextFormat::WHITE . $fac . TextFormat::AQUA . " | " . TextFormat::LIGHT_PURPLE . number_format((int)$amount) . " STR";
            }
        }
        $this->floatingTexts["Faction Power"] = [
            new Position(64.5708, 59.1154, -277.5752, $level),
            $text
        ];
        //$connector->executeSelectQuery("SELECT username, luckyBlocks FROM stats ORDER BY luckyBlocks DESC LIMIT 10;", function(array $rows) use($level) {
        //    $place = 1;
        //    $text = TextFormat::GOLD . TextFormat::BOLD . "TOP 10 LB MINERS";
        //    foreach($rows as [
        //            "username" => $username,
        //            "luckyBlocks" => $amount
        //    ]) {
        //        $text .= "\n" . TextFormat::BOLD . TextFormat::YELLOW . "$place. " . TextFormat::RESET . TextFormat::WHITE . $username . TextFormat::AQUA . " | " . TextFormat::LIGHT_PURPLE . number_format((int)$amount);
        //        $place++;
        //    }
        //    $this->floatingTexts["Lucky Blocks"] = [
        //        new Position(70.4527, 59.1154, -279.5268, $level),
        //        $text
        //    ];
        //});
    }
}