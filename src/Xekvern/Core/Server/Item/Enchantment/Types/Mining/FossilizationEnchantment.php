<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FossilizationEnchantment extends Enchantment
{

    /**
     * FossilizationEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Fossilization", self::RARITY_GODLY, "Increase your chance of getting a sacred stone.", self::BREAK, ItemFlags::DIG, 3);
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
                $chance = 7500 - $level;
                if (mt_rand(1, 7500) >= $chance) {
                    $item = new SacredStone();
                    if(!$player->getInventory()->canAddItem($item->getItemForm())) {
                        $player->getDataSession()->addToInbox($item->getItemForm());
                        $player->sendMessage(Translation::AQUA . "Your inventory is full your item has been added to your /inbox");
                        return;
                    }
                    $player->getDataSession()->addToInbox($item->getItemForm());
                }
            }
        };
    }
}
