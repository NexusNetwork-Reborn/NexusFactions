<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\Block;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Forms\CustomTagForm;
use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;

class CustomTag extends ClickableItem {

    const CUSTOM_TAG = "CustomTag";

    /**
     * CustomTag constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::GREEN . TextFormat::BOLD . "Custom Tag";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Create your own custom tag to display on";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "your chat with " . TextFormat::WHITE . "/tag";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "WARNING:" . TextFormat::RESET . TextFormat::GRAY . "Do NOT write anything inappropriate such as";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "any offensive slurs! You've been warned!";
        parent::__construct(VanillaItems::NAME_TAG(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::CUSTOM_TAG => new StringTag(self::CUSTOM_TAG),
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
        $player->sendForm(new CustomTagForm());
    }
}