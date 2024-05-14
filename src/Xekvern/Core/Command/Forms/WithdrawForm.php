<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Item\Types\CrateKeyNote;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Types\XPNote;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Dropdown;
use libs\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class WithdrawForm extends CustomForm {

    /**
     * WithdrawForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Withdraw";
        $balance = $player->getDataSession()->getBalance();
        $xp = $player->getXpManager()->getCurrentTotalXp();
        $swuses = $player->getDataSession()->getSellWandUses();
        $crateHandler = $player->getCore()->getServerManager()->getCrateHandler();
        $legendary = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::LEGENDARY));
        $epic = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::EPIC));
        $ultra = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::ULTRA));
        $boss = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::BOSS));
        $elements = [];
        $elements[] = new Dropdown("Options", "What would you like to withdraw?", [
            "Balance ($" . number_format($balance) . ")",
            "XP (" . number_format($xp) . ")",
            "Sell Wand Uses (" . number_format($swuses) . ")",
            "Legendary Crate Keys (" . number_format($legendary) . ")",
            "Epic Crate Keys (" . number_format($epic) . ")",
            "Ultra Crate Keys (" . number_format($ultra) . ")",
            "Boss Crate Keys (" . number_format($boss) . ")"
        ]);
        $elements[] = new Input("Amount", "How many would you like to withdraw?");
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
        if(count($player->getInventory()->getContents()) === $player->getInventory()->getSize()) {
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Full Inventory", TextFormat::GRAY . "Clear out your inventory!");
            $player->sendMessage(Translation::getMessage("fullInventory"));
            $player->playErrorSound();
            return;
        }
        /** @var Dropdown $dropdown */
        $dropdown = $this->getElementByName("Options");
        $option = $dropdown->getOption($data->getInt("Options"));
        $amount = $data->getString("Amount");
        if(!is_numeric($amount)) {
            $player->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        $amount = (int)$amount;
        if($amount <= 0) {
            $player->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        $balance = $player->getDataSession()->getBalance();
        $xp = $player->getXpManager()->getCurrentTotalXp();
        $swuses = $player->getDataSession()->getSellWandUses();
        $crateHandler = $player->getCore()->getServerManager()->getCrateHandler();
        $legendary = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::LEGENDARY));
        $epic = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::EPIC));
        $ultra = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::ULTRA));
        $boss = $player->getDataSession()->getKeys($crateHandler->getCrate(Crate::BOSS));
        switch($option) {
            case "Balance ($" . number_format($balance) . ")":
                if($amount > $balance) {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                if($amount > 100000000) {
                    $player->sendMessage(Translation::RED . "You can only withdraw with the max of " . TextFormat::YELLOW . "$1,000,000,000");
                    $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Withdraw Limit", TextFormat::GRAY . "You cannot withdraw more than the limit!");
                    $player->playErrorSound();
                    return;
                }
                $player->getDataSession()->subtractFromBalance($amount);
                $player->getInventory()->addItem((new MoneyNote($amount, $player->getName()))->getItemForm());
                break;
            case "XP (" . number_format($xp) . ")":
                if($amount > $xp) {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                if($amount > 1000000) {
                    $player->sendMessage(Translation::RED . "You can only withdraw with the max of " . TextFormat::LIGHT_PURPLE . "1,000,000 XP");
                    $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Withdraw Limit", TextFormat::GRAY . "You cannot withdraw more than the limit!");
                    $player->playErrorSound();
                    return;
                }
                $player->getXpManager()->subtractXp($amount);
                $player->getInventory()->addItem((new XPNote($amount, $player->getName()))->getItemForm());
                break;
            case "Sell Wand Uses (" . number_format($swuses) . ")":
                if($amount > $swuses) {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                if($amount > 100000) {
                    $player->sendMessage(Translation::RED . "You can only withdraw with the max of " . TextFormat::AQUA . "100,000 SW");
                    $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Withdraw Limit", TextFormat::GRAY . "You cannot withdraw more than the limit!");
                    $player->playErrorSound();
                    return;
                }
                $player->getDataSession()->subtractFromSellWandUses($amount);
                $player->getInventory()->addItem((new SellWandNote($amount))->getItemForm());
                break;
            case "Legendary Crate Keys (" . number_format($legendary) . ")":
                if($amount > $legendary) {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                $crate = $crateHandler->getCrate(Crate::LEGENDARY);
                $player->getDataSession()->removeKeys($crate, $amount);
                $player->getInventory()->addItem((new CrateKeyNote($crate->getName(), $amount, $player->getName()))->getItemForm());
                break;
            case "Epic Crate Keys (" . number_format($epic) . ")":
                if($amount > $epic) {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                $crate = $crateHandler->getCrate(Crate::EPIC);
                $player->getDataSession()->removeKeys($crate, $amount);
                $player->getInventory()->addItem((new CrateKeyNote($crate->getName(), $amount, $player->getName()))->getItemForm());
                break;
            case "Ultra Crate Keys (" . number_format($ultra) . ")":
                if($amount > $ultra) {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                $crate = $crateHandler->getCrate(Crate::ULTRA);
                $player->getDataSession()->removeKeys($crate, $amount);
                $player->getInventory()->addItem((new CrateKeyNote($crate->getName(), $amount, $player->getName()))->getItemForm());
                break;
            case "Boss Crate Keys (" . number_format($boss) . ")":
                if($amount > $boss) {
                    $player->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                $crate = $crateHandler->getCrate(Crate::BOSS);
                $player->getDataSession()->removeKeys($crate, $amount);
                $player->getInventory()->addItem((new CrateKeyNote($crate->getName(), $amount, $player->getName()))->getItemForm());
                break;
        }
    }
}