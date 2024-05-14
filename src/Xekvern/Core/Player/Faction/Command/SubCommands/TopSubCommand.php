<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;

class TopSubCommand extends SubCommand {

    /**
     * TopSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("top", "/faction top <power/value> [page = 1]");
        $this->registerArgument(0, new IntegerArgument("page = 1", true));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        switch($args[1]) {
            case "value":
                $text = $this->showValue();
                $sender->sendMessage($text);
                break;
            case "power":
                $text = $this->showSTR();
                $sender->sendMessage($text);
                break;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                return;
                break;
        }
    }

    /**
     * @return string
     */
    public function showSTR(): string {
        $str = [];
        foreach(Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getFactions() as $name => $fac) {
            $str[$name] = $fac->getStrength();
        }
        arsort($str);
        $top = TextFormat::GOLD . TextFormat::BOLD . "TOP 10 STRONGEST FACTIONS";
        $i = 0;

        foreach($str as $fac => $strength) {
            $i++;
            if($i < 11) {
                $top .= "\n" . TextFormat::BOLD . TextFormat::YELLOW . "$i. " . TextFormat::RESET . TextFormat::DARK_AQUA . $fac . TextFormat::YELLOW . " | " . TextFormat::LIGHT_PURPLE  . $strength . " STR";
            }
        }
        return $top;
    }

    /**
     * @return string
     */
    public function showValue(): string {
        $val = [];
        foreach(Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getFactions() as $name => $fac) {
            $val[$name] = $fac->getClaimValue();
        }
        arsort($val);
        $place = 0;
        $text = TextFormat::GOLD . TextFormat::BOLD . "TOP 10 RICHEST FACTIONS";
        foreach($val as $fac => $amount) {
            $place++;
            if($place < 11) {
                $text .= "\n" . TextFormat::BOLD . TextFormat::YELLOW . "$place. " . TextFormat::RESET . TextFormat::WHITE . $fac . TextFormat::AQUA . " | " . TextFormat::LIGHT_PURPLE . "$" . number_format((int)$amount);
            }
        }
        return $text;
    }
}