<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Gamble\Command\SubCommands;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Gamble\Event\LotteryBuyEvent;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Gamble\GambleHandler;

class BuySubCommand extends SubCommand
{

    /**
     * BuySubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("buy", "/lottery buy <amount>");
        $this->registerArgument(0, new IntegerArgument("amount"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        if ((!is_numeric($args[1])) or (int)$args[1] <= 0) {
            $sender->sendMessage(Translation::getMessage("notNumeric"));
            return;
        }
        $tickets = (int)($args[1]);
        $price = $tickets * GambleHandler::TICKET_PRICE;
        if ($price > $sender->getDataSession()->getBalance()) {
            $sender->sendMessage(Translation::RED . "You don't have money to do this action.");
            return;
        }
        $this->getCore()->getPlayerManager()->getGambleHandler()->addDraws($sender, $tickets);
        $ev = new LotteryBuyEvent($sender, $tickets);
        $ev->call();
        $sender->getDataSession()->subtractFromBalance($price);
        $sender->sendMessage(Translation::getMessage("buy", [
            "amount" => TextFormat::GREEN . "x" . number_format($tickets),
            "item" => TextFormat::AQUA . "Lottery Tickets",
            "price" => TextFormat::LIGHT_PURPLE . "$" . number_format($price),
        ]));
    }
}