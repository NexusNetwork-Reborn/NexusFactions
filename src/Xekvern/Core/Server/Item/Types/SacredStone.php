<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\Block;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\BlazeShootSound;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Utils\ClickableItem;

class SacredStone extends ClickableItem {

    const SACRED_STONE = "SacredStone";

    /**
     * SacredStone constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "Sacred Stone";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "This stone seems to be containing something..";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Click anywhere have a " . TextFormat::RED . TextFormat::BOLD . "CHANCE" . TextFormat::RESET . TextFormat::GRAY . " of uncovering something.";
        parent::__construct(VanillaItems::NETHER_QUARTZ(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::SACRED_STONE => new StringTag(self::SACRED_STONE),
            "UniqueId" => new StringTag(uniqid())
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
        if(mt_rand(1, 5) == 1) {
            $kits = $player->getCore()->getServerManager()->getKitHandler()->getSacredKits();
            $kit = $kits[array_rand($kits)];
            $player->getWorld()->addSound($player->getEyePos(), new BlazeShootSound());
            $player->getInventory()->addItem((new HolyBox($kit))->getItemForm());
        }
        else {
            $player->getWorld()->addSound($player->getEyePos(), new AnvilUseSound());
        }
        /** @var PlayerInventory $inventory */
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}