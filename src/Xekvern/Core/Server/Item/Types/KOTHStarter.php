<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\Combat\Koth\Task\StartKOTHGameTask;
use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;

class KOTHStarter extends ClickableItem {

    const KOTH = "KOTH";

    /**
     * KOTHStarter constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "KOTH Starter";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Tap anywhere to start a KOTH game.";
        parent::__construct(ExtraVanillaItems::FIREWORKS(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::KOTH => new StringTag(self::KOTH)
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
        if (Nexus::getInstance()->isInGracePeriod()) {
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Grace Period", TextFormat::GRAY . "You can't do this action while on grace period!");
            $player->sendMessage(Translation::RED . "You can't use this while the server is on grace period!");
            $player->playErrorSound();
            return;
        }
        $kothManager = Nexus::getInstance()->getPlayerManager()->getCombatHandler();
        if($kothManager->getKOTHGame() !== null) {
            $player->sendMessage(Translation::getMessage("kothRunning"));
            return;
        }
        $kothManager->initiateKOTHGame();
        Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new StartKOTHGameTask(Nexus::getInstance()), 20);
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}