<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Kit\Kit;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Task\HolyBoxAnimationTask;
use Xekvern\Core\Server\Item\Utils\ClickableItem;

class HolyBox extends ClickableItem {

    const SACRED_KIT = "SacredKit";

    /**
     * HolyBox constructor.
     *
     * @param Kit $kit
     */
    public function __construct(Kit $kit) {
        $customName = TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . "{$kit->getName()} Holy Box";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Place in spawn to open this box for a chance";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "to get a sacred kit permanently!";
        parent::__construct(VanillaBlocks::CHEST()->asItem(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::SACRED_KIT => new StringTag($kit->getName()),
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
     *
     * @throws TranslatonException
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        if($player->getWorld()->getFolderName() !== $player->getCore()->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()) {
            $player->sendMessage(Translation::getMessage("onlyInSpawn"));
            return;
        }
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
        $player->getCore()->getScheduler()->scheduleRepeatingTask(new HolyBoxAnimationTask($player, $player->getCore()->getServerManager()->getKitHandler()->getKitByName($tag->getString(HolyBox::SACRED_KIT))), 1);
    }
}