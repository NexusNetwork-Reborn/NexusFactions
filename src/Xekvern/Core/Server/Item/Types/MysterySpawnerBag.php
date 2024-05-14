<?php

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ChestCloseSound;

class MysterySpawnerBag extends ClickableItem {

    const MYSTERY_SPAWNER = "MysterySpawnerBag";

    /**
     * MysterySpawnerBag constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "Mystery Spawner Bag";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use this item to obtain a random spawner.";
        parent::__construct(VanillaBlocks::CHEST()->asItem(), $customName, $lore, [], [
            self::MYSTERY_SPAWNER => new StringTag(self::MYSTERY_SPAWNER),
        ]);
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     *
     * @throws TranslatonException
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        $ps = $blockClicked->getPosition();
        $rewards = [
            (new MonsterSpawner(EntityIds::ZOMBIE))->getItemForm(),
            (new MonsterSpawner(EntityIds::SQUID))->getItemForm(),
            (new MonsterSpawner(EntityIds::BLAZE))->getItemForm(),
            (new MonsterSpawner(EntityIds::IRON_GOLEM))->getItemForm(),
        ];
        $item = $rewards[array_rand($rewards)];
        if($player->getInventory()->canAddItem($item)) {
            $player->getInventory()->addItem($item);
        }
        else {
            $player->getWorld()->dropItem($player->getPosition(), $item);
        }
        $player->broadcastSound(new ChestCloseSound(), [$player]);
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int {
        return 1;
    }   
}