<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Utils;

use Exception;
use pocketmine\block\GlazedTerracotta;
use Xekvern\Core\Server\World\Tile\Generator;
use Xekvern\Core\Nexus;
use pocketmine\world\format\Chunk;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Server\World\Block\MonsterSpawner;
use Xekvern\Core\Server\World\Tile\MonsterSpawnerTile;
use Xekvern\Core\Server\World\WorldHandler;

class Claim
{

    /** @var int */
    private $chunkX;

    /** @var int */
    private $chunkZ;

    /** @var Faction */
    private $faction;

    /** @var int */
    private $value;

    /** @var bool */
    private $needsUpdate = false;

    /**
     * Claim constructor.
     *
     * @param int $chunkX
     * @param int $chunkZ
     * @param Faction $faction
     * @param int $value
     */
    public function __construct(int $chunkX, int $chunkZ, Faction $faction, int $value = 0)
    {
        $this->chunkX = $chunkX;
        $this->chunkZ = $chunkZ;
        $this->faction = $faction;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getChunkX(): int
    {
        return $this->chunkX;
    }

    /**
     * @return int
     */
    public function getChunkZ(): int
    {
        return $this->chunkZ;
    }

    /**
     * @return Faction
     */
    public function getFaction(): Faction
    {
        return $this->faction;
    }

    /**
     * @param Faction $faction
     */
    public function setFaction(Faction $faction): void
    {
        $this->faction = $faction;
        $this->scheduleUpdate();
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    /**
     * @param Chunk $chunk
     */
    public function recalculateValue(Chunk $chunk): void
    {
        $tiles = $chunk->getTiles();
        $this->value = 0;
        try {
            foreach ($tiles as $tile) {
                $block = $tile->getBlock();
                if ($tile instanceof MonsterSpawnerTile and $block instanceof MonsterSpawner) {
                    $this->value += WorldHandler::getSpawnerValue($tile->getEntityTypeId()) * $tile->getStacked();
                }
                if ($tile instanceof Generator and $block instanceof GlazedTerracotta) {
                    $this->value += WorldHandler::getGeneratorValue($block->getColor()) * $tile->getStack();
                }
            }
        } catch(Exception $e) {
            Nexus::getInstance()->getLogger()->notice("[Recalculate Chunk] Unsuccessfully recalculated chunk!");
        }
    }

    /**
     * @return bool
     */
    public function needsUpdate(): bool
    {
        return $this->needsUpdate;
    }

    public function scheduleUpdate(): void
    {
        $this->needsUpdate = true;
    }

    public function updateAsync(): void
    {
        if ($this->needsUpdate) {
            $this->needsUpdate = false;
            $x = $this->chunkX;
            $z = $this->chunkZ;
            $faction = $this->faction->getName();
            $connector = Nexus::getInstance()->getMySQLProvider()->getConnector();
            $connector->executeUpdate("REPLACE INTO claims(faction, chunkX, chunkZ, value) VALUES(?, ?, ?, ?)", "siii", [
                $faction,
                $x,
                $z,
                $this->getValue()
            ]);
        }
    }

    public function update(): void
    {
        if ($this->needsUpdate) {
            $database = Nexus::getInstance()->getMySQLProvider()->getDatabase();
            $this->needsUpdate = false;
            $x = $this->chunkX;
            $z = $this->chunkZ;
            $faction = $this->faction->getName();
            $stmt = $database->prepare("REPLACE INTO claims(faction, chunkX, chunkZ, value) VALUES(?, ?, ?, ?)");
            $stmt->bind_param("siii", $faction, $x, $z, $this->value);
            $stmt->execute();
            $stmt->close();
        }
    }
}