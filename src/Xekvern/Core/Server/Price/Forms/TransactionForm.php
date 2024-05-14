<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price\Forms;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Price\Event\ItemBuyEvent;
use Xekvern\Core\Server\Price\Event\ItemSellEvent;
use Xekvern\Core\Server\Price\PriceEntry;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Input;
use libs\form\element\Label;
use libs\form\element\Toggle;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TransactionForm extends CustomForm {

    /** @var PriceEntry */
    private $priceEntry;

    /**
     * TransactionForm constructor.
     *
     * @param NexusPlayer $player
     * @param PriceEntry $entry
     */
    public function __construct(NexusPlayer $player, PriceEntry $entry) {
        $this->priceEntry = $entry;
        $title = TextFormat::BOLD . TextFormat::AQUA . $entry->getName();
        $elements = [];
        $message = TextFormat::GRAY . "Your balance: " . TextFormat::WHITE . "$" . number_format($player->getDataSession()->getBalance());
        $elements[] = new Label("Balance", $message);
        $elements[] = new Toggle("Enable Buy", "Buy");
        $elements[] = new Toggle("Enable Sell", "Sell");
        $buyPrice = $entry->getBuyPrice();
        if($buyPrice === null) {
            $buyPrice = TextFormat::WHITE . "Not buyable";
        }
        else {
            $buyPrice = TextFormat::WHITE . "$buyPrice";
        }
        $elements[] = new Label("Buy Price", TextFormat::DARK_AQUA . "Unit buy price: " . $buyPrice);
        $sellPrice = $entry->getSellPrice();
        if($sellPrice === null) {
            $sellPrice = TextFormat::WHITE . "Not sellable";
        }
        else {
            $sellPrice = TextFormat::WHITE . "$sellPrice";
        }
        $elements[] = new Label("Sell Price", TextFormat::DARK_AQUA . "Unit sell price: " . $sellPrice);
        $elements[] = new Input("Amount", "Amount");
        parent::__construct($title, $elements);
    }

    /**
     * @param Player $player
     * @param CustomFormResponse $data
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, CustomFormResponse $data): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $buyToggle = $data->getBool("Enable Buy");
        $sellToggle = $data->getBool("Enable Sell");
        $all = $data->getAll();
        $amount = (int)$all["Amount"];
        $item = clone $this->priceEntry->getItem();
        if(!$item instanceof Item) {
            return;
        }
        if($this->priceEntry->getPermission() !== null and (!$player->hasPermission($this->priceEntry->getPermission()))) {
            $player->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if($buyToggle === true and $sellToggle === true) {
            $player->sendMessage(Translation::getMessage("turnOnAToggle"));
            return;
        }
        if($buyToggle === false and $sellToggle === false) {
            $player->sendMessage(Translation::getMessage("turnOnAToggle"));
            return;
        }
        if($sellToggle === true and $this->priceEntry->getSellPrice() === null) {
            $player->sendMessage(Translation::getMessage("notSellable"));
            return;
        }
        if($buyToggle === true and $this->priceEntry->getBuyPrice() === null) {
            $player->sendMessage(Translation::getMessage("notBuyable"));
            return;
        }
        if($amount <= 0 or (!is_numeric($amount))) {
            $player->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        $inventory = $player->getInventory();
        if($buyToggle === true) {
            $price = $this->priceEntry->getBuyPrice() * $amount;
            $balance = $player->getDataSession()->getBalance();
            if($price > $balance) {
                $player->sendMessage(Translation::getMessage("turnOnAToggle"));
                return;
            }
            $item->setCount($amount * $this->priceEntry->getItem()->getCount());
            $inventory->addItem($item);
            $player->getDataSession()->subtractFromBalance($price);
            $player->sendMessage(Translation::getMessage("buy", [
                "amount" => TextFormat::GREEN . "x" . number_format($item->getCount()),
                "item" => TextFormat::DARK_GREEN . $this->priceEntry->getName(),
                "price" => TextFormat::LIGHT_PURPLE . "$" . number_format($price),
            ]));
            $event = new ItemBuyEvent($player, $item, $price);
            $event->call();
            return;
        }
        if($sellToggle === true) {
            $price = $this->priceEntry->getSellPrice() * $amount;
            $item->setCount($amount * $this->priceEntry->getItem()->getCount());
            if(!$inventory->contains($item)) {
                $player->sendMessage(Translation::getMessage("nothingSellable"));
                return;
            }
            $inventory->removeItem($item);
            $player->getDataSession()->addToBalance($price);
            $player->sendMessage(Translation::getMessage("sell", [
                "amount" => TextFormat::GREEN . number_format($item->getCount()),
                "item" => TextFormat::DARK_GREEN . $this->priceEntry->getName(),
                "price" => TextFormat::LIGHT_PURPLE . "$" . number_format($price),
            ]));
            $event = new ItemSellEvent($player, $item, $price);
            $event->call();
            return;
        }
        return;
    }
}