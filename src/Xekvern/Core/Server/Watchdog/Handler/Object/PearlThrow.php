<?php

namespace Xekvern\Core\Server\Watchdog\Handler\Object;

use pocketmine\player\Player;
use pocketmine\world\Position;

class PearlThrow {

    /** @var Player */
    private $player;

    /** @var int */
    private $thrownAt;

    /** @var int */
    private $completedAt;

    /** @var int */
    private $boundId;

    /** @var Position|Null */
    private $landingLocation;

    /**
     * PearlThrow constructor.
     *
     * @param Player $player
     * @param int $boundId
     * @param int|null $thrown
     */
    public function __construct(Player $player, int $boundId, int $thrown = null) {
        $this->player = $player;
        $this->thrownAt = $thrown ?? microtime(true);
        $this->completedAt = -1;
        $this->boundId = $boundId;
        $this->landingLocation = null;
    }

    /**
     * Gets the player for the throw
     * @return Player - The player who threw the pearl
     */
    public function getPlayer(): ?Player {
        return $this->player;
    }

    /**
     * Gets the time taken for the pearl to land from being thrown.
     * @return int - time taken
     */
    public function getTimeCompleted(): int {
        if(!$this->isCompleted()) {
            return -1;
        }
        else {
            return $this->completedAt - $this->thrownAt;
        }
    }

    /**
     * Gets the time the pearl landed at (-1 if still in air)
     * @return int - The time the pearl landed.
     */
    public function getLandingTime(): int {
        return $this->completedAt;
    }

    /**
     * Gets the location where the pearl landed.
     * @return Position|Null
     */
    public function getLandingLocation(): ?Position {
        return $this->landingLocation;
    }

    /**
     * Gets the time the pearl was thrown
     * @return int - The time the pearl was thrown
     */
    public function getThrownTime(): int {
        return $this->thrownAt;
    }

    /**
     * Gets whether the throw has been completed.
     * @return Bool - Whether or not the pearl has landed.
     */
    public function isCompleted(): bool {
        return !($this->completedAt === -1);
    }

    /**
     * Sets the time that the pearl landed.
     *
     * @param int $time - The time the pearl landed.
     * @param Position $position - Where the pearl landed
     *
     * @return void
     */
    public function setCompleted(int $time, Position $position): void {
        $this->completedAt = $time;
        $this->landingLocation = $position;
        return;
    }

    /**
     * Gets the entity runtime id for the thrown pearl
     * @return int - the entity id
     */
    public function getPearlEntityId(): int {
        return $this->boundId;
    }
}