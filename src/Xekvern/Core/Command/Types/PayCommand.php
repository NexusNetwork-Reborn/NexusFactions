<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\Args\TargetArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PayCommand extends Command {

    /**
     * PayCommand constructor.
     */
    public function __construct() {
        parent::__construct("pay", "Pay a player.", "/pay <player:target> <amount: int>");
        $this->registerArgument(0, new TargetArgument("player"));
        $this->registerArgument(1, new IntegerArgument("amount"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, $commandLabel, array $args): void {
        if($sender instanceof NexusPlayer) {
            if(isset($args[1])) {
                $player = $sender->getServer()->getPlayerExact($args[0]);
                if(!$player instanceof NexusPlayer) {
                    $sender->sendMessage(Translation::getMessage("invalidPlayer"));       
                    return;
                }
                if($player->getName() === $sender->getName()) {
                    $sender->sendMessage(Translation::getMessage("invalidPlayer"));       
                    return;
                }
                if($player->isLoaded() === false) {
                    $sender->sendMessage(Translation::getMessage("errorOccurred"));
        
                    return;
                }
                $amount = (int)$args[1];
                if((!is_numeric($args[1])) or $amount <= 0) {
                    $sender->sendMessage(Translation::getMessage("invalidAmount"));        
                    return;
                }
                if($sender->getDataSession()->getBalance() < $amount) {
                    $sender->sendMessage(Translation::getMessage("invalidAmount"));       
                    return;
                }
                $sender->getDataSession()->subtractFromBalance($amount);
                $player->getDataSession()->addToBalance($amount);
                $sender->sendMessage(Translation::getMessage("payMoneyTo", [
                    "amount" => TextFormat::LIGHT_PURPLE . "$" . number_format($amount),
                    "name" => TextFormat::GREEN . $player->getName()
                ]));
                $player->sendMessage(Translation::getMessage("receiveMoneyFrom", [
                    "amount" => TextFormat::LIGHT_PURPLE . "$" . number_format($amount),
                    "name" => TextFormat::GREEN . $sender->getName()
                ]));
                return;
            }
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));

            return;
        }
        $sender->sendMessage(Translation::getMessage("noPermission"));
    }
}