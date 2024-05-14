<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;

class JackpotEnchantment extends Enchantment
{

    /**
     * JackpotEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Jackpot", Rarity::RARE, "Have a chance to earn money while mining.", self::BREAK, ItemFlags::DIG, 10);
        $this->callable = function (BlockBreakEvent $event, int $level) {
            $block = $event->getBlock();
            $player = $event->getPlayer();
            if (!$player instanceof NexusPlayer) {
                return;
            }
            if ($event->isCancelled()) {
                return;
            }
            $amount = mt_rand(100, 1000);
            $amount *= $level;
            if ($block->getTypeId() === BlockTypeIds::STONE) {
                $chance = 500 - $level;
                if (mt_rand(1, 500) >= $chance) {
                    $player->getDataSession()->addToBalance($amount);
                    $player->sendTip(TextFormat::BOLD . TextFormat::YELLOW . " + $$amount");
                }
            }
        };
    }
}
