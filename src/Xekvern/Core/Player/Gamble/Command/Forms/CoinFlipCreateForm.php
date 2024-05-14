<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Gamble\Command\Forms;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Gamble\Command\Forms\CoinFlipListForm;
use Xekvern\Core\Player\Gamble\Command\Inventory\SelectColorInventory;
use Xekvern\Core\Translation\Translation;

class CoinFlipCreateForm extends CustomForm {

    /**
     * CoinFlipCreateForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $title = TextFormat::BOLD . TextFormat::YELLOW . "Coin Flip";
        $balance = $player->getDataSession()->getBalance();
        $elements = [];
        $elements[] = new Input("Amount", TextFormat::GRAY . "Your balance: " . TextFormat::WHITE . "$" . number_format($player->getDataSession()->getBalance()));
        parent::__construct($title, $elements);
    }

    /**
     * @param Player $player
     * @param CustomFormResponse $data
     *
     * @throws TranslationException
     */
    public function onSubmit(Player $player, CustomFormResponse $data): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        $amount = $data->getString("Amount");
        if(!is_numeric($amount)) {
            $player->sendMessage(Translation::getMessage("notNumeric"));
            return;
        }
        if(Nexus::getInstance()->getPlayerManager()->getGambleHandler()->getActiveCoinFlip($player) !== null) {
            $player->sendMessage(Translation::getMessage("activeCoinFlip"));
            return;
        }
        if(Nexus::getInstance()->getPlayerManager()->getGambleHandler()->getCoinFlip($player) !== null) {
            $player->sendMessage(Translation::getMessage("existingCoinFlip"));
            return;
        }
        $amount = (int)$amount;
        if($amount <= 0) {
            $player->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        if($amount < 10000) {
            $player->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        if($player->getDataSession()->getBalance() <= $amount) {
            $player->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        $player->removeCurrentWindow();
        $player->sendDelayedWindow(new SelectColorInventory($amount));
    }
}