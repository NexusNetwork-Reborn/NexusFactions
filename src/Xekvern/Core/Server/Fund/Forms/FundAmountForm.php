<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Fund\Forms;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\TranslatonException;
use libs\form\CustomForm;
use libs\form\CustomFormResponse;
use libs\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Fund\Utils\FundType;
use Xekvern\Core\Server\Fund\Utils\MergedFund;
use Xekvern\Core\Translation\Translation;

class FundAmountForm extends CustomForm {

    /** @var MergedFund */
    private $mergedFundTarget;

    /**
     * FundAmountForm constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player, MergedFund $mergedFund) {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Fund Amount";
        $this->mergedFundTarget = $mergedFund;
        $elements = [];
        $elements[] = new Input("Amount", "Enter the amount you want to fund to " . TextFormat::BOLD . TextFormat::YELLOW . $mergedFund->getFundInformation()->getName());
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
        if ($data === null) {
            return;
        }
        $amount = $data->getString("Amount");
        if (!is_numeric($amount)) {
            $player->sendMessage(Translation::getMessage("invalidAmount"));
            return;
        }
        $amount = intval($amount);
        if ($amount <= 0) {
            $player->sendMessage(Translation::RED . "The amount given must have a value greater than 0.");
            return;
        }
        $type = $this->mergedFundTarget->getFundConfigable()->getType();
        switch ($type) {
            case FundType::XP:
                $player_xp = $player->getXpManager()->getXpLevel();
                if ($amount > $player_xp) {
                    $player->sendMessage(Translation::RED . "You don't have enough XP to donate to this goal.");
                    return;
                }
                break;
            case FundType::MONEY:
                $player_money = $player->getDataSession()->getBalance();
                if ($amount > $player_money) {
                    $player->sendMessage(Translation::RED . "You don't have enough money to donate to this goal.");
                    return;
                }
                break;

            case FundType::POWER:
                $player_power = $player->getDataSession()->getPower(); 
                if ($amount > $player_power) {
                    $player->sendMessage(Translation::RED . "You don't have enough power to donate to this goal.");
                    return;
                }
                break;
        }
        $left_amount = $this->mergedFundTarget->getFundConfigable()->getGoal() - $this->mergedFundTarget->getFundInformation()->getAmount();
        if ($amount > $left_amount) {
            $player->sendMessage(Translation::RED . "The amount you want to fund is greater than the amount needed to reach the goal.");
            return;
        }
        $player->sendForm(new FundConfirmationForm($this->mergedFundTarget, $amount));
    }
}