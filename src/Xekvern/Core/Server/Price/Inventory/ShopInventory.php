<?php

namespace Xekvern\Core\Server\Price\Inventory;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Price\ShopPlace;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Price\Forms\TransactionForm;
use Xekvern\Core\Server\Price\PriceEntry;
use Xekvern\Core\Translation\Translation;

class ShopInventory extends InvMenu {

    /** @var ShopPlace[] */
    private $places;

    /** @var PriceEntry */
    private $entries;

    /** @var string */
    private $inventoryItems = "MainInventory"; 

    /**
     * ShopInventory constructor.
     *
     * @param ShopPlace[] $places
     */
    public function __construct() {
        parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenu::TYPE_DOUBLE_CHEST));
        $this->places = Nexus::getInstance()->getServerManager()->getPriceHandler()->getPlaces();
        $this->initMainItems($this->places);
        $this->setName(TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Shop");
        $this->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction): void {
            $action = $transaction->getAction();
            $player = $transaction->getPlayer();
            $slot = $action->getSlot();
            if(!$player instanceof NexusPlayer) {
                return;
            }
            if($this->inventoryItems === "MainInventory") {
                if(isset($this->places[$slot])) {
                    $place = $this->places[$slot];
                    $this->getInventory()->clearAll();
                    $this->initListItems($place);
                    $this->inventoryItems = "ListInventory";
                }
            } elseif ($this->inventoryItems === "ListInventory") {
                if($slot === 53) {
                    $this->getInventory()->clearAll();
                    $this->places = Nexus::getInstance()->getServerManager()->getPriceHandler()->getPlaces();
                    $this->initMainItems($this->places);
                    $this->inventoryItems = "MainInventory";
                }
                if(isset($this->entries[$slot])) {
                    $entry = $this->entries[$slot];
                    if($entry->getPermission() !== null and (!$player->hasPermission($entry->getPermission()))) {
                        $player->sendMessage(Translation::getMessage("noPermission"));
                        return;
                    }
                    $player->removeCurrentWindow($action->getInventory());
                    Nexus::getInstance()->getScheduler()->scheduleDelayedTask(new class($entry, $player) extends Task {
    
                        /** @var PriceEntry */
                        private $entry;
    
                        /** @var NexusPlayer */
                        private $player;
    
                        /**
                         *  constructor.
                         *
                         * @param PriceEntry $entry
                         * @param NexusPlayer $player
                         */
                        public function __construct(PriceEntry $entry, NexusPlayer $player) {
                            $this->entry = $entry;
                            $this->player = $player;
                        }
    
                        /**
                         * @param int $currentTick
                         */
                        public function onRun(): void {
                            if($this->player->isOnline() and (!$this->player->isClosed())) {
                                $this->player->sendForm(new TransactionForm($this->player, $this->entry));
                            }
                        }
                    }, 20);
                }
            }
        }));
    }

    /**
     * @param ShopPlace[] $places
     */
    public function initMainItems(array $places): void {
        $glass = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::WHITE())->asItem();
        $glass->setCustomName(" ");
        for($i = 0; $i < 54; $i++) {
            if(($i >= 20 and $i <= 24) or ($i >= 29 and $i <= 33)) {
                $place = array_shift($places);
                if($place instanceof ShopPlace) {
                    $display = $place->getItem();
                    $this->places[$i] = $place;
                    $display->setCustomName(TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . $place->getName());
                    $display->setLore([TextFormat::RESET . TextFormat::GRAY . "Tap to view this category."]);
                    $this->getInventory()->setItem($i, $display);
                }   
            }
        }
    }

    /**
     * @param ShopPlace[] $places
     */
    public function initListItems(ShopPlace $place): void {
        $glass = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::WHITE())->asItem();
        $glass->setCustomName(" ");
        $entries = $place->getEntries();
        for($i = 0; $i < 54; $i++) {
            $entry = array_shift($entries);
            if($entry instanceof PriceEntry) {
                $display = clone $entry->getItem();
                $this->entries[$i] = $entry;
                $lore = $display->getLore();
                $add = [];
                $add[] = "";
                if($entry->getBuyPrice() !== null) {
                    $add[] = TextFormat::RESET . TextFormat::GRAY . "Buy Price(ea): " . TextFormat::GREEN . "$" . number_format($entry->getBuyPrice());
                }
                else {
                    $add[] = TextFormat::RESET . TextFormat::GRAY . "Buy Price(ea): " . TextFormat::RED . "Not buyable";
                }
                if($entry->getSellPrice() !== null) {
                    $add[] = TextFormat::RESET . TextFormat::GRAY . "Sell Price(ea): " . TextFormat::GREEN . "$" . number_format($entry->getSellPrice());
                }
                else {
                    $add[] = TextFormat::RESET . TextFormat::GRAY . "Sell Price(ea): " . TextFormat::RED . "Not sellable";
                }
                if($entry->getPermission() !== null) {
                    $add[] = TextFormat::RESET . TextFormat::GRAY . "Rankup Required: " . TextFormat::GREEN . "Yes";
                } 
                $display->setLore(array_merge($lore, $add));
                $this->getInventory()->setItem($i, $display);
            }
        }
        $home = VanillaBlocks::OAK_DOOR()->asItem();
        $home->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Home");
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Return to the main";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "shopping menu";
        $home->setLore($lore);
        $this->getInventory()->setItem(53, $home);
    }
}