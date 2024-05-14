<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\Block;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\BlazeShootSound;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class Drops extends CustomItem {

    const ITEM_LIST = "ItemList";

    /**
     * Drops constructor.
     *
     * @param string $player
     * @param Item[] $items
     */
    public function __construct(string $player, array $items) {
        $customName = TextFormat::RESET . TextFormat::GREEN . TextFormat::BOLD . "$player's Drops";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Click/Tap anywhere to redeem drops. Be sure to clear inventory!";
        $tags = [];
        foreach($items as $item) {
            $tags[] = $item->nbtSerialize();
        }
        parent::__construct(VanillaItems::NETHER_STAR(), $customName, $lore, [], [
            self::ITEM_LIST => new ListTag($tags)
        ]);
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        $list = $tag->getListTag(self::ITEM_LIST);
        if($list === null){
            return;
        }
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
         foreach($list as $tag) {
            $item = Item::nbtDeserialize($tag);
            if($inventory->canAddItem($item)) {
                $inventory->addItem($item);
            } else {
                $player->getWorld()->dropItem($player->getPosition(), $item);
            }
        }
        $player->getWorld()->addSound($player->getEyePos(), new BlazeShootSound());
    }
}