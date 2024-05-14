<?php

namespace Xekvern\Core\Server\Fund\Utils;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

final class MergedFund {
    public function __construct(
        private readonly FundInformation $fundInformation,
        private readonly FundConfigable  $fundConfigable,
    ) {
    }

    public function isFunded(): bool {
        return $this->fundInformation->getAmount() >= $this->fundConfigable->getGoal();
    }

    public function getFundInformation(): FundInformation {
        return $this->fundInformation;
    }

    public function getFundConfigable(): FundConfigable {
        return $this->fundConfigable;
    }

    public function createItem(Item $item): Item {
        $item->setCustomName(TextFormat::RESET . TextFormat::AQUA . $this->getFundInformation()->getName());

        $type = $this->getFundConfigable()->getType();
        switch ($type) {
            case FundType::XP:
                $funded_amount = TextFormat::LIGHT_PURPLE . number_format($this->getFundInformation()->getAmount()) . TextFormat::BOLD . " XP";
                $amount_needed = TextFormat::LIGHT_PURPLE . number_format($this->getFundConfigable()->getGoal()) . TextFormat::BOLD . " XP";
                break;
            case FundType::POWER:
                $funded_amount = TextFormat::RED . number_format($this->getFundInformation()->getAmount()) . TextFormat::BOLD . " POWER";
                $amount_needed = TextFormat::RED . number_format($this->getFundConfigable()->getGoal()) . TextFormat::BOLD . " POWER";
                break;
            default:
                $funded_amount = TextFormat::GOLD . "$" . number_format($this->getFundInformation()->getAmount());
                $amount_needed = TextFormat::GOLD . "$" . number_format($this->getFundConfigable()->getGoal());
                break;
        }

        $progress = $this->getFundInformation()->getAmount() / $this->getFundConfigable()->getGoal();
        $item->setLore([
            " ",
            TextFormat::RESET . TextFormat::YELLOW . "Amount Funded" . TextFormat::GRAY . ": " . TextFormat::RESET . $funded_amount,
            TextFormat::RESET . TextFormat::YELLOW . "Amount Needed" . TextFormat::GRAY . ": " . TextFormat::RESET . $amount_needed,
            TextFormat::RESET . TextFormat::YELLOW . "Progress" . TextFormat::GRAY . ": " . TextFormat::RESET . TextFormat::GRAY . "[" . TextFormat::WHITE . str_repeat(TextFormat::GREEN . "|" . TextFormat::RESET, (int)($progress * 10)) . str_repeat(TextFormat::GRAY . "|" . TextFormat::RESET, 10 - (int)($progress * 10)) . TextFormat::GRAY . "] (" . TextFormat::WHITE . round($progress * 100, 2) . "%" . TextFormat::GRAY . ")"
        ]);

        return $item;
    }

    public static function make(FundInformation $fundInformation, FundConfigable $fundConfigable): MergedFund {
        return new MergedFund($fundInformation, $fundConfigable);
    }
}