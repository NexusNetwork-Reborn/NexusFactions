<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Gamble\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\Gamble\Command\SubCommands\BuySubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Gamble\GambleHandler;

class LotteryCommand extends Command {

    /**
     * LotteryCommand constructor.
     */
    public function __construct() {
        parent::__construct("lottery", "Manage lottery", "/lottery <buy> <amount>");
        $this->addSubCommand(new BuySubCommand());
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(isset($args[0])) {
            $subCommand = $this->getSubCommand($args[0]);
            if($subCommand !== null) {
                $subCommand->execute($sender, $commandLabel, $args);
                return;
            }
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $sender->sendMessage(TextFormat::AQUA . "================" . TextFormat::LIGHT_PURPLE . "================");
        $sender->sendMessage(" ");
        $sender->sendMessage(TextFormat::AQUA . TextFormat::BOLD . "          Lottery");
        $manager = $this->getCore()->getPlayerManager()->getGambleHandler();
        $own = $manager->getDrawsFor($sender);
        $tickets = $manager->getTotalDraws();
        $total = $tickets * GambleHandler::TICKET_PRICE;
        if($tickets === 0) {
            $percentage = 0;
        }
        else {
            $percentage = $own / $tickets;
            $percentage = round($percentage * 100, 2);
        }
        $sender->sendMessage(" ");
        $sender->sendMessage(TextFormat::LIGHT_PURPLE . " Your tickets: " . TextFormat::WHITE . number_format($own) . TextFormat::AQUA . "($percentage%)");
        $sender->sendMessage(TextFormat::LIGHT_PURPLE . " Total tickets: " . TextFormat::WHITE . number_format($tickets));
        $sender->sendMessage(TextFormat::LIGHT_PURPLE . " Pot: " . TextFormat::WHITE . "$" . number_format($total));
        $sender->sendMessage(TextFormat::LIGHT_PURPLE . " Time Left: " . TextFormat::WHITE . Utils::secondsToTime($this->getCore()->getPlayerManager()->getGambleHandler()->getDrawer()->getTimeLeft()));
        $sender->sendMessage(" ");
        $sender->sendMessage(TextFormat::LIGHT_PURPLE . "================" . TextFormat::AQUA . "================");
        $sender->sendMessage(Translation::getMessage("usageMessage", [
            "usage" => $this->getUsage()
        ]));
        return;
    }
}