<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\Types\LuckyBlock;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;

class LuckEnchantment extends Enchantment
{

    /**
     * LuckEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Luck", Rarity::MYTHIC, "Increase your chance of getting a lucky block with a slightly better success rate.", self::BREAK, ItemFlags::DIG, 2);
        $this->callable = function (BlockBreakEvent $event, int $level) {
            $block = $event->getBlock();
            $player = $event->getPlayer();
            if (!$player instanceof NexusPlayer) {
                return;
            }
            if ($event->isCancelled()) {
                return;
            }
            if ($block->getTypeId() === BlockTypeIds::STONE) {
                $chance = 250 - $level;
                if (mt_rand(1, 150) >= $chance) {
                    $item = new LuckyBlock(mt_rand(25, 100));
                    $player->getDataSession()->addToInbox($item->getItemForm());
                    $player->sendMessage(Translation::getMessage("luckyBlockFound"));
                    $player->getDataSession()->addLuckyBlocksMined();
                }
            }
        };
    }
}
