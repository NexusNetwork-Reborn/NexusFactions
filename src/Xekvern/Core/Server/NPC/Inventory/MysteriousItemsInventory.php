<?php

namespace Xekvern\Core\Server\NPC\Inventory;

use Xekvern\Core\Player\NexusPlayer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Types\CosmeticBag;
use Xekvern\Core\Server\Item\Types\GeneratorsBag;
use Xekvern\Core\Server\Item\Types\Head;
use Xekvern\Core\Server\Item\Types\Lootbox;
use Xekvern\Core\Server\Item\Types\MonthlyCrate;
use Xekvern\Core\Server\Item\Types\MysterySpawnerBag;
use Xekvern\Core\Server\NPC\Forms\GemShopConfirmationForm;
use Xekvern\Core\Translation\Translation;

class MysteriousItemsInventory extends InvMenu {

    private $items = [];

    /**
     * MysteriousItemsInventory constructor.
     */
    public function __construct() {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_CHEST));
        $this->initItems();
        $this->setName(TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "Gems Shop");
        $this->setListener(self::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $slot = $action->getSlot();
            $player = $transaction->getPlayer();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            $item = $action->getSourceItem();
            $player->removeCurrentWindow();
            $this->handlePurchase($player, $slot);
            return;
        }));
    }

    public function initItems(): void {
        $this->addItem((new CosmeticBag())->getItemForm(), 1); 
        $this->addItem((new MysterySpawnerBag())->getItemForm(), 3); 
        $this->addItem((new GeneratorsBag())->getItemForm(), 5); 
        $this->addItem((new Lootbox("Royalty", TextFormat::GOLD . "ROYALTY"))->getItemForm(), 10);
        $this->addItem((new Lootbox("Husk", TextFormat::BOLD . TextFormat::DARK_GRAY. "Husky"))->getItemForm(), 15); 
        $this->addItem((new MonthlyCrate())->getItemForm(), 30); 
    }

    public function addItem(Item $item, int $price): void {
        $display_item = clone $item;
        $lore = [];
        $lore[] = " ";
        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST " . TextFormat::RESET . TextFormat::GREEN . $price . " GEMS";
        $display_item->setLore(array_merge($item->getLore(), $lore));
        $this->inventory->addItem($display_item);
        $this->items[] = ['item' => $item, 'price' => $price];
    }

    public function handlePurchase(NexusPlayer $player, int $slot): void {
        $itemData = $this->items[$slot] ?? null;
        if (!$itemData) {
            return; 
        }
        $item = $itemData['item'];
        $price = $itemData['price'];
        $playerGems = $player->getDataSession()->getGems(); 
        if ($playerGems < $price) {
            $player->playErrorSound();
            $player->sendMessage(Translation::RED . "You do not have enough gems to purchase this item.");
            return;
        }
        $player->sendForm(new GemShopConfirmationForm($this, $slot, $item, $price));
    }

    public function confirmPurchase(NexusPlayer $player, int $slot): void {
        $itemData = $this->items[$slot] ?? null;
        if (!$itemData) {
            return; 
        }
        $item = $itemData['item'];
        $price = $itemData['price'];
        $player->getDataSession()->subtractGems($price); 
        $player->getInventory()->addItem($item);
        $player->sendMessage(TextFormat::GREEN . "You have purchased " . $item->getName() . TextFormat::RESET . TextFormat::GREEN . " for " . $price . " gems.");
    }
}