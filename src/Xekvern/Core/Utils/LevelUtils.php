<?php

namespace Xekvern\Core\Utils;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class LevelUtils
{

    /**
     * Gets the block relative to the provided face.
     *
     * @param Block $block - Block to use
     * @param int $face - the face.
     *
     * @return Block
     */
    public static function getRelativeBlock(Block $block, int $face): Block
    {
        $newVector = $block->getSide($face);
        return $block->getPosition()->getWorld()->getBlock($newVector->getPosition());
    }

    /**
     * Alias for Level::getBlockAt()
     *
     * @param Position $pos - Position to get the block at.
     *
     * @return Block|null
     */
    public static function getBlockWhere(Position $pos): ?Block
    {
        $level = $pos->getWorld();
        if ($level === null) {
            return null;
        } else {
            return $level->getBlock($pos);
        }
    }

    /**
     * @param World $level
     * @param Vector3|null $spawn
     *
     * @return Position
     */
    public static function getSafeSpawn(World $level, ?Vector3 $spawn = null): Position
    {
        if (!($spawn instanceof Vector3) || $spawn->y < 1) {
            $spawn = $level->getSpawnLocation();
        }

        $max = $level->getMaxY();
        $v = $spawn->floor();
        $level->loadChunk($spawn->getX() >> 4, $spawn->getZ() >> 4);
        $chunk = $level->getOrLoadChunkAtPosition($v);
        if ($chunk === null) {
            return new Position($spawn->x, $v->y, $spawn->z, $level);
        }
        $x = (int) $v->x;
        $z = (int) $v->z;
        $y = (int) min($max - 2, $v->y);
        $wasAir = $level->getBlockAt($x, $y - 1, $z)->getTypeId() === BlockTypeIds::AIR; //TODO: bad hack, clean up
        for (; $y > $level->getMinY(); --$y) {
            if ($level->getBlockAt($x, $y, $z)->isFullCube()) {
                if ($wasAir) {
                    $y++;
                    break;
                }
            } else {
                $wasAir = true;
            }
        }

        for (; $y >= $level->getMinY() && $y < $max; ++$y) {
            if (!$level->getBlockAt($x, $y + 1, $z)->isFullCube()) {
                if (!$level->getBlockAt($x, $y, $z)->isFullCube()) {
                    return new Position($spawn->x, $y === (int) $spawn->y ? $spawn->y : $y, $spawn->z, $level);
                }
            } else {
                ++$y;
            }
        }

        return new Position($spawn->x, $y, $spawn->z, $level);
    }
}
