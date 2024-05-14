<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Item\ItemIds;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\ClickableItem;

class CrateKeyNote extends ClickableItem {

    const CRATE = "Crate";

    const AMOUNT = "Amount";

    /**
     * CrateKeyNote constructor.
     *
     * @param string $crateName
     * @param int $keys
     * @param string $withdrawer
     */
    public function __construct(string $crateName, int $keys, string $withdrawer = "Admin") {
        $customName = TextFormat::RESET . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . number_format((int)$keys) . " $crateName Keys"  . TextFormat::RESET . TextFormat::GRAY . " (Right-Click)";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Withdrawn by " . TextFormat::RESET . TextFormat::WHITE . $withdrawer;
        parent::__construct(VanillaBlocks::TRIPWIRE_HOOK()->asItem(), $customName, $lore, [], [
            self::CRATE => new StringTag($crateName),
            self::AMOUNT => new IntTag($keys)
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
        $crate = $tag->getString(CrateKeyNote::CRATE);
        $amount = $tag->getInt(CrateKeyNote::AMOUNT);
        $crate = $player->getCore()->getServerManager()->getCrateHandler()->getCrate($crate);
        $player->getDataSession()->addKeys($crate, $amount);
        $player->playXpLevelUpSound();
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}