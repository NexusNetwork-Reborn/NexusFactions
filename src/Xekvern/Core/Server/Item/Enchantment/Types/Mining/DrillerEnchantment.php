<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\block\Block;

class DrillerEnchantment extends Enchantment
{
    /** @var int[] */
    public static array $lastBreakFace = [];

    /**
     * DrillerEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Driller", self::RARITY_GODLY, "Drills a 3x3 hole", self::BREAK, ItemFlags::DIG, 1);
        $this->callable = function (BlockBreakEvent $event, int $level) {
            $block = $event->getBlock();
            $player = $event->getPlayer();
            if (!$player instanceof NexusPlayer) {
                return;
            }
            if ($event->isCancelled()) {
                return;
            }
            $facing = $this->getDirectionFor($player);
            $facing = $this->getDirectionFor($player);
            $item = $player->getInventory()->getItemInHand();
            if ($facing === null) {
                return;
            }
            $radius = $level * 2;
            $radius = $radius % 2 === 0 ? $radius + 1 : $radius;
            [$x, $y, $z] = [$block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ()];
            $radius = $radius / 2;
            match ($facing) {
                Facing::NORTH => $z += $radius,
                Facing::EAST => $x += $radius,
                Facing::SOUTH => $z -= $radius,
                Facing::WEST => $x -= $radius,
            };
            $blocks = [];
            for ($i = 0; $i < $radius; $i++) {
                for ($j = 0; $j < $radius; $j++) {
                    $blocks[] = $player->getWorld()->getBlockAt((int)$x + $i, $y, (int)$z + $j);
                    $blocks[] = $player->getWorld()->getBlockAt((int)$x - $i, $y, (int)$z - $j);
                    $blocks[] = $player->getWorld()->getBlockAt((int)$x + $i, $y, (int)$z - $j);
                    $blocks[] = $player->getWorld()->getBlockAt((int)$x - $i, $y, (int)$z + $j);
                }
            }
            /** @var Block $block */
            foreach ($blocks as $block) {
                $player->getWorld()->useBreakOn($block->getPosition(), $item, $player, true);
            }
        };
    }

    public function getDirectionFor(Player $player): ?int {
        $rotation = intval($player->getLocation()->getYaw()) - 90 % 360;
        if ($rotation < 0) {
            $rotation += 360.0;
        }

        if ((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)) {
            return Facing::NORTH;
        } elseif (45 <= $rotation and $rotation < 135) {
            return Facing::EAST;
        } elseif (135 <= $rotation and $rotation < 225) {
            return Facing::SOUTH;
        } elseif (225 <= $rotation and $rotation < 315) {
            return Facing::WEST;
        } else {
            return null;
        }
    }
}
