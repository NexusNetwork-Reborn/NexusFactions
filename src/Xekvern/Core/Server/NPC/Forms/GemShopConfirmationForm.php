<?php

namespace Xekvern\Core\Server\NPC\Forms;

use Xekvern\Core\Player\NexusPlayer;
use libs\form\ModalForm;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\NPC\Inventory\MysteriousItemsInventory;

class GemShopConfirmationForm extends ModalForm
{

    private $inventory;
    private $slot;
    private $item;
    private $price;

    /**
     * GemShopConfirmationForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(MysteriousItemsInventory $inventory, int $slot, Item $item, int $price)
    {
        $this->inventory = $inventory;
        $this->slot = $slot;
        $this->item = $item;
        $this->price = $price;
        $title = TextFormat::BOLD . TextFormat::AQUA . "Confirm Purchase";
        $text = "This item would cost an amount of " . TextFormat::BOLD . TextFormat::GREEN . $price . " Gems " . TextFormat::RESET . " to purchase.";
        parent::__construct($title, $text);
    }

    /**
     * @param Player $player
     * @param bool $choice
     */
    public function onSubmit(Player $player, bool $choice): void
    {
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if ($choice == true) {
            $this->inventory->confirmPurchase($player, $this->slot);
            return;
        }
        return;
    }
}