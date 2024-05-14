<?php

namespace Xekvern\Core\Server\Fund\Forms;

use Xekvern\Core\Player\Gamble\Event\CoinFlipLoseEvent;
use Xekvern\Core\Player\Gamble\Event\CoinFlipWinEvent;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\ModalForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Fund\Utils\FundType;
use Xekvern\Core\Server\Fund\Utils\MergedFund;

class FundConfirmationForm extends ModalForm {

    /** @var MergedFund */
    private $mergedFundTarget;

    /** @var int */
    private $fundAmount;

    /**
     * FundConfirmationForm constructor.
     *
     * @param MergedFund $mergedFund
     */
    public function __construct(MergedFund $mergedFund, int $amount) {
        $this->mergedFundTarget = $mergedFund;
        $this->fundAmount = $amount;
        $title = TextFormat::BOLD . TextFormat::YELLOW . "Fund Confirmation";
        $text = "Are you sure you would like to fund $amount to " . TextFormat::BOLD . TextFormat::YELLOW . $mergedFund->getFundConfigable()->getName() . TextFormat::RESET . "?";
        parent::__construct($title, $text);
    }

    /**
     * @param Player $player
     * @param bool $choice
     *
     * @throws TranslatonException
     */
    public function onSubmit(Player $player, bool $choice): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if($choice == true) {
            $fund_information = $this->mergedFundTarget->getFundInformation();
            $fund_information->setAmount($fund_information->getAmount() + $this->fundAmount);
            $type = $this->mergedFundTarget->getFundConfigable()->getType();
            switch ($type) {
                case FundType::XP:
                    $player->getXpManager()->setXPLevel($player->getXpManager()->getXpLevel() - $this->fundAmount);
                    $fundAmount = $this->fundAmount . " XP";
                    break;
                case FundType::MONEY:
                    $player->getDataSession()->subtractFromBalance($this->fundAmount); 
                    break;
                case FundType::POWER:
                    $player->getDataSession()->subtractFromPower($this->fundAmount);
                    break;
            }
            $player->sendMessage(Translation::RED . "You have successfully funded " . $this->fundAmount . " to " . TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . $this->mergedFundTarget->getFundConfigable()->getName() . TextFormat::RESET . TextFormat::GRAY . " goal.");
        }
        return;
    }
}