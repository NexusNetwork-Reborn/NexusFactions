<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Area;

use pocketmine\world\Position;
use pocketmine\world\World;

class Area {

    /** @var int */
    private $priority;

    /** @var string */
    private $name;

    /** @var Position */
    private $firstPosition;

    /** @var Position */
    private $secondPosition;

    /** @var bool */
    private $pvpFlag;

    /** @var bool */
    private $editFlag;

    /** @var World|null */
    private $level;

    /**
     * Area constructor.
     *
     * @param int $priority
     * @param string $name
     * @param Position $firstPosition
     * @param Position $secondPosition
     * @param bool $pvpFlag
     * @param bool $editFlag
     *
     * @throws AreaException
     */
    public function __construct(int $priority, string $name, Position $firstPosition, Position $secondPosition, bool $pvpFlag, bool $editFlag) {
        $this->firstPosition = $firstPosition;
        $this->secondPosition = $secondPosition;
        $this->name = $name;
        $this->priority = $priority;
        $this->level = $firstPosition->getWorld()->getDisplayName() === $secondPosition->getWorld()->getDisplayName() ? $firstPosition->getWorld() : null;
        if($this->level === null) {
            throw new AreaException("Area \"$name\"'s first position's level does not equal the second position's level.");
        }
        $this->pvpFlag = $pvpFlag;
        $this->editFlag = $editFlag;
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
        return $minX <= $position->getX() and $maxX >= $position->getX() and $minY <= $position->getY() and
            $maxY >= $position->getY() and $minZ <= $position->getZ() and $maxZ >= $position->getZ() and
            $this->level->getDisplayName() === $level->getDisplayName();
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
     * @return World
     */
    public function getLevel(): World {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPriority(): int {
        return $this->priority;
    }

    /**
     * @return bool
     */
    public function getPvpFlag(): bool {
        return $this->pvpFlag;
    }

    /**
     * @return bool
     */
    public function getEditFlag(): bool {
        return $this->editFlag;
    }
}