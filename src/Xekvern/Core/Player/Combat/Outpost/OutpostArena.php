<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Outpost;

use pocketmine\Server;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Player\Faction\Faction;
use Exception;
use libs\muqsit\arithmexp\Util;
use Xekvern\Core\Utils\Utils;

class OutpostArena {

    const CAPTURING_TIME = 600; // -- 5 mins
    const UNCAPTURE_TIME = 120; // -- 2 minutes
    const CAPTURED_TIME = 300; // -- 1 minute and 20 seconds

    /** @var Nexus */
    private $core;

    /** @var Position */
    private $firstPosition;

    /** @var Position */
    private $secondPosition;

    /** @var string|null */
    private $currentFaction = null;   
    /** @var string|null */
    private $capturedFaction = null;   
    /** @var int */
    private int $nextReward; 
    /** @var int */
    private ?int $progressOutpost;
    /** @var int */
    private int $spam = 0;

    /**
     * OutpostArena constructor.
     *
     * @param Position $firstPosition
     * @param Position $secondPosition
     * @param int $objectiveTime
     *
     * @throws OutpostException
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->nextReward = self::CAPTURED_TIME;
        $this->progressOutpost = self::CAPTURING_TIME;
        $this->firstPosition = new Position(269, 0, 233, $this->core->getServer()->getWorldManager()->getWorldByName("warzone"));
        $this->secondPosition = new Position(260, 13, 242, $this->core->getServer()->getWorldManager()->getWorldByName("warzone"));
    }

    /**
     * @throws Exception
     */
    public function tick(): void { 
        $this->nextReward--;
        $config = Nexus::getInstance()->getConfig();
        $file = Nexus::getInstance()->getPlayerManager()->getCombatHandler()->getOutpostData();
        $players = Nexus::getInstance()->getServer()->getOnlinePlayers();
        $factionHandler = Nexus::getInstance()->getPlayerManager()->getFactionHandler();
        $actual = $file->get("actual", null);

        $world = Nexus::getInstance()->getServer()->getWorldManager()->getWorldByName("warzone");
        foreach($world->getPlayers() as $player) {
            /** @var NexusPlayer $player */
            if($player->getFloatingText("Outpost") === null) {
                $outpostText = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::DARK_RED . "OUTPOST",
                    TextFormat::WHITE . "Controlled by: " . TextFormat::RED . "No One",
                    TextFormat::WHITE . "Control Time: " . TextFormat::GREEN . Utils::secondsToCD($this->progressOutpost),
                ]);
                $player->addFloatingText(new Position(265.0237, 11.6246, 237.8858, $world), "Outpost", $outpostText);
            }
            $text = $player->getFloatingText("Outpost");
            if (Nexus::getInstance()->isInGracePeriod()) {
                $outpostTextX = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::DARK_RED . "OUTPOST",
                    TextFormat::RED . "In Grace Period"
                ]);
                $text->update($outpostTextX);
                $text->sendChangesTo($player);
                return;
            }
            if(!is_null($actual)) {
                $outpostText2 = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::DARK_RED . "OUTPOST",
                    TextFormat::WHITE . "Controlled by: " . TextFormat::RED . $actual,
                    TextFormat::WHITE . "Reward Time: " . TextFormat::GREEN . Utils::secondsToCD($this->nextReward),
                    TextFormat::WHITE . "Overtake Control Time: " . TextFormat::GREEN . Utils::secondsToCD($this->progressOutpost),
                ]);
                $text->update($outpostText2);
                $text->sendChangesTo($player);
            } else {
                $outpostText1 = implode(TextFormat::RESET . "\n", [
                    TextFormat::BOLD . TextFormat::DARK_RED . "OUTPOST",
                    TextFormat::WHITE . "Controlled by: " . TextFormat::GOLD . "No One",
                    TextFormat::WHITE . "Control Time: " . TextFormat::GREEN . Utils::secondsToCD($this->progressOutpost),
                ]);
                $text->update($outpostText1);
                $text->sendChangesTo($player);
            }
        }
        if (1 > count($factionHandler->getFactions())) {
            return;
        }
        if (Nexus::getInstance()->isInGracePeriod()) {
            return;
        }
        if (!is_null($actual)) {
            if ($this->progressOutpost > self::CAPTURING_TIME) {
                $this->progressOutpost = self::UNCAPTURE_TIME;
            }
            if (is_null($factionHandler->getFaction($actual))) {
                $file->set("actual", null);
                $file->save();
                $this->progressOutpost = self::CAPTURING_TIME;
                return;
            } else if (is_null($this->currentFaction)) {
                foreach ($players as $player) {
                    if(
                        $player instanceof NexusPlayer && 
                        $player->isLoaded() && 
                        $player->isAlive() && 
                        $this->isPositionInside($player->getPosition()) && 
                        !is_null($player->getDataSession()->getFaction()) &&
                        $player->getDataSession()->getFaction()->getName() !== $actual &&
                        !self::searchPlayersFaction($actual)
                    ) {
                        $faction = $factionHandler->getFaction($actual);
                        $this->currentFaction = $player->getDataSession()->getFaction()->getName();
                        foreach($faction->getOnlineMembers() as $members) {
                            if ((time() - $this->spam) > 2) {
                                $members->sendMessage(Translation::RED . "The outpost that your faction is holding is now being overtaken by " . TextFormat::RED . $this->currentFaction);
                                $this->spam = time();
                            }
                        }
                        $player->sendTip(TextFormat::RESET . TextFormat::AQUA . "Overtaking... " . TextFormat::GRAY . "| " . TextFormat::YELLOW . Utils::secondsToCD($this->progressOutpost));
                        return;
                    }
                }
                $this->progressOutpost = self::UNCAPTURE_TIME;
            } else {
                if (is_null($factionHandler->getFaction($this->currentFaction)) || !self::searchPlayersFaction($this->currentFaction)) {
                    $this->currentFaction = null;
                    $this->progressOutpost = self::UNCAPTURE_TIME;
                    return;
                }
            }
            $this->progressOutpost--;
            if (0 >= $this->progressOutpost) {
                $this->core->getInstance()->getServer()->broadcastMessage(TextFormat::BOLD . TextFormat::DARK_RED . "[Outpost] " . TextFormat::RESET . TextFormat::AQUA . $actual . TextFormat::WHITE . " has lost the outpost! " . "Use the command /pvp to go to the outpost!");
                $file->set("actual", null);
                $file->save();
                $this->progressOutpost = self::CAPTURING_TIME;
            }
            $actual = $file->get("actual", "");
            $faction = $factionHandler->getFaction($actual);
            if ($this->nextReward > 1) {
                foreach ($players as $player) {
                    if (
                        $player instanceof NexusPlayer &&
                        $player->isLoaded() &&
                        $player->isAlive() &&
                        $this->isPositionInside($player->getPosition()) &&
                        !is_null($player->getDataSession()->getFaction())
                    ) {
                        $player->sendTip(TextFormat::RESET . TextFormat::RED . "Rewards... " . TextFormat::GRAY . "| " . TextFormat::YELLOW . Utils::secondsToCD($this->nextReward));
                        return;
                    }
                }
            }
            if (0 >= $this->nextReward && $faction instanceof Faction) {
                $this->nextReward = self::CAPTURED_TIME;
                Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::DARK_RED . "[Outpost] " . TextFormat::RESET . TextFormat::AQUA . $actual . TextFormat::WHITE . " received rewards such as" . TextFormat::YELLOW . " Faction Value, Power & EXP" . TextFormat::WHITE . " for holding the outpost! Use the command /pvp to go to the outpost!");
                $this->Capture($faction);
            }
            return;
        }
        if (is_null($this->currentFaction)) {
            foreach ($players as $player) {
                if (
                    $player instanceof NexusPlayer &&
                    $player->isLoaded() &&
                    $player->isAlive() &&
                    $this->isPositionInside($player->getPosition()) &&
                    !is_null($player->getDataSession()->getFaction())
                ) {
                    $this->currentFaction = $player->getDataSession()->getFaction()->getName();
                    if ((time() - $this->spam) > 8) {
                        $this->core->getInstance()->getServer()->broadcastMessage(TextFormat::BOLD . TextFormat::DARK_RED . "[Outpost] " . TextFormat::RESET . TextFormat::AQUA . $this->currentFaction . TextFormat::WHITE . " is now capturing the outpost! " . "Use the command /pvp to go to the outpost!");
                        $this->spam = time();
                    }
                    return;
                }
            }
            $this->progressOutpost = self::CAPTURING_TIME;
        } else {
            if (is_null($factionHandler->getFaction($this->currentFaction)) || !self::searchPlayersFaction($this->currentFaction)) {
                $this->currentFaction = null;
                $this->progressOutpost = self::CAPTURING_TIME;
                return;
            }
        }
        $this->progressOutpost--;
        if($this->progressOutpost > 1) {
            foreach ($players as $player) {
                if (
                    $player instanceof NexusPlayer &&
                    $player->isLoaded() &&
                    $player->isAlive() &&
                    $this->isPositionInside($player->getPosition()) &&
                    !is_null($player->getDataSession()->getFaction()) &&
                    $player->getDataSession()->getFaction()->getName() === $this->currentFaction
                ) {
                    $player->sendTip(TextFormat::RESET . TextFormat::AQUA . "Claiming... " . TextFormat::GRAY . "| " . TextFormat::YELLOW . Utils::secondsToCD($this->progressOutpost));
                    return;
                }
            }
        }
        if (0 >= $this->progressOutpost) {
            Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::DARK_RED . "[Outpost] " . TextFormat::RESET . TextFormat::AQUA . $this->currentFaction . TextFormat::WHITE . " has captured the outpost! Use the command /pvp to go to the outpost!");
            $file->set("actual", $this->currentFaction);
            $file->save();
            $this->currentFaction = null;
            $this->progressOutpost = self::UNCAPTURE_TIME;
            $this->nextReward = self::CAPTURED_TIME;
        }
    }

    /**
     * @return Position
     */
    public function getFirstPosition(): Position {
        return $this->firstPosition;
    }

    /**
     * @return Position
     */
    public function getSecondPosition(): Position {
        return $this->secondPosition;
    }

    /** 
     * @return string|null
     */
    public function getCapturedFaction(): ?string {
        return $this->capturedFaction;
    }

    /** 
     * @return int
     */
    public function getCaptureProgress(): int {
        return $this->progressOutpost;
    }

    /** 
     * @return int
     */
    public function getNextRewardTime(): int {
        return $this->nextReward;
    }


    /**
     * @param Position $position
     *
     * @return bool
     */
    public function isPositionInside(Position $position): bool {
        $level = $position->getWorld();
        if($level === null) {
            return false;
        }
        $firstPosition = $this->firstPosition;
        $secondPosition = $this->secondPosition;
        $minX = min($firstPosition->getX(), $secondPosition->getX());
        $maxX = max($firstPosition->getX(), $secondPosition->getX());
        $minY = min($firstPosition->getY(), $secondPosition->getY());
        $maxY = max($firstPosition->getY(), $secondPosition->getY());
        $minZ = min($firstPosition->getZ(), $secondPosition->getZ());
        $maxZ = max($firstPosition->getZ(), $secondPosition->getZ());
        return $minX <= $position->getX() and $maxX >= $position->getFloorX() and
            $minY <= $position->getY() and $maxY >= $position->getFloorY() and
            $minZ <= $position->getZ() and $maxZ >= $position->getFloorZ() and
            $this->firstPosition->getWorld()->getDisplayName() === $level->getDisplayName();
    }

    /**
     * @param Faction $faction
     * @return void
     * This is called when the outpost is captured
     */
    public function Capture(Faction $faction): void {
        //register the reward task to give the rewards every time it runs
        foreach($faction->getOnlineMembers() as $player) {
            if($player instanceof NexusPlayer and $player->isLoaded()) {
                $power = mt_rand(2, 6);
                $faction->addMoney(250000);
                $player->getDataSession()->addToPower($power);
            }
            $player->sendMessage(Translation::GREEN . "Your faction has received $100,000 and all online members have received " . $power . " STR from the outpost!");
        }
    }

    /**
     * @param string $faction
     */
    public function searchPlayersFaction(string $faction): bool
    {
        $found = false;
        $factionHandler = Nexus::getInstance()->getPlayerManager()->getFactionHandler();
        $faction = $factionHandler->getFaction($faction);
        if ($faction instanceof Faction) {
            foreach ($faction->getOnlineMembers() as $player) {
                if ($player->isAlive() && $this->isPositionInside($player->getPosition())) {
                    $found = true;
                }
            }
        }
        return $found;
    }
}