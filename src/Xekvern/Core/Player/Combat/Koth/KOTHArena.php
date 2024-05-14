<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Combat\Koth;

use Xekvern\Core\Player\Combat\Koth\Event\KOTHCaptureEvent;
use Xekvern\Core\Server\Item\Types\KOTHLootbag;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class KOTHArena {

    /** @var Position */
    private $firstPosition;

    /** @var Position */
    private $secondPosition;

    /** @var int[] */
    private $progresses = [];

    /** @var int */
    private $objectiveTime;

    /** @var bool */
    private $started = false;

    /**
     * KOTHArena constructor.
     *
     * @param string $name
     * @param Position $firstPosition
     * @param Position $secondPosition
     * @param int $objectiveTime
     *
     * @throws KOTHException
     */
    public function __construct(string $name, Position $firstPosition, Position $secondPosition, int $objectiveTime) {
        $this->firstPosition = $firstPosition;
        $this->secondPosition = $secondPosition;
        if($firstPosition->getWorld() === null or $secondPosition->getWorld() === null) {
            throw new KOTHException("KOTH arena \"$name\" position levels are invalid.");
        }
        if($firstPosition->getWorld()->getDisplayName() !== $secondPosition->getWorld()->getDisplayName()) {
            throw new KOTHException("KOTH arena \"$name\" position levels are not the same.");
        }
        $this->objectiveTime = $objectiveTime;
    }

    /**
     * @throws TranslatonException
     */
    public function tick(): void {
        /** @var NexusPlayer $player */
        foreach($this->firstPosition->getWorld()->getPlayers() as $player) {
            if(!isset($this->progresses[$player->getUniqueId()->toString()])) {
                $this->progresses[$player->getUniqueId()->toString()] = 0;
                continue;
            }
            if($this->isPositionInside($player->getPosition()) and (!$player->isFlying())) {
                ++$this->progresses[$player->getUniqueId()->toString()];
                $percentage = round(($this->progresses[$player->getUniqueId()->toString()] / $this->objectiveTime) * 100);
                $player->sendTitle(TextFormat::BOLD . TextFormat::BLUE . "Capturing" .  str_repeat(".", ($this->objectiveTime - $this->progresses[$player->getUniqueId()->toString()]) % 4), "$percentage%");
            }
            if($this->progresses[$player->getUniqueId()->toString()] >= $this->objectiveTime) {
                $ev = new KOTHCaptureEvent($player);
                $ev->call();
                $player->getInventory()->addItem((new KOTHLootbag())->getItemForm());
                Nexus::getInstance()->getPlayerManager()->getCombatHandler()->endKOTHGame();
                Nexus::getInstance()->getServer()->broadcastMessage(TextFormat::BOLD . TextFormat::DARK_BLUE . "[King of The Hill] " . TextFormat::RESET . TextFormat::LIGHT_PURPLE . $player->getName() . TextFormat::YELLOW . " has won the KOTH game!");
                $player->sendMessage(Translation::getMessage("kothReward"));
                $this->progresses = [];
                $this->started = false;
                return;
            }
            $kothWorld = Nexus::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
            if($player->getFloatingText("KOTH") == null ) {
                $player->addFloatingText(Position::fromObject(new Position(-324.4934, 131.76932, 287.4744, $kothWorld), $kothWorld), "KOTH", TextFormat::BOLD . TextFormat::BLUE . "KING OF THE HILL");
            }
        }
    }

    /**
     * @return int
     */
    public function getObjectiveTime(): int {
        return $this->objectiveTime;
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
     * @return bool
     */
    public function hasStarted(): bool {
        return $this->started;
    }

    /**
     * @param bool $started
     */
    public function setStarted(bool $started): void {
        $this->started = $started;
    }
}