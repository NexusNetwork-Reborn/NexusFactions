<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\Block;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\BlazeShootSound;
use pocketmine\world\sound\ClickSound;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class PowerNote extends CustomItem {

    const POWER = "Power";

    /**
     * PowerNote constructor.
     *
     * @param int $amount
     * @param string $withdrawer
     */
    public function __construct(int $amount) {
        $customName = TextFormat::RESET . TextFormat::DARK_RED . TextFormat::BOLD . "Power Note" . TextFormat::RESET . TextFormat::GRAY . " (Right-Click)";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use this note to obtain " . TextFormat::RED . number_format($amount) . " Power";
        parent::__construct(VanillaItems::PAPER(), $customName, $lore, [], [
            self::POWER => new IntTag($amount)
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
        $amount = $tag->getInt(self::POWER);
        $player->getWorld()->addSound($player->getEyePos(), new ClickSound());
        $player->getDataSession()->addToPower($amount);
        /** @var PlayerInventory $inventory */
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}