<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Currency;

use Xekvern\Core\Command\Utils\Args\IntegerArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Faction\Modules\PermissionsModule;

class WithdrawSubCommand extends SubCommand {

    /**
     * WithdrawSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("withdraw", "/faction withdraw <amount>");
        $this->registerArgument(0, new IntegerArgument("amount"));
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
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $senderFaction = $sender->getDataSession()->getFaction();
        if($senderFaction === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if(!$senderFaction->getPermissionsModule()->hasPermission($sender, PermissionsModule::PERMISSION_WITHDRAW)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $amount = (int)$args[1];
        if(!is_numeric($amount)) {
            $sender->sendMessage(Translation::getMessage("notNumeric"));
            return;
        }
        $amount = max(0, $amount);
        if($senderFaction->getBalance() < $amount) {
            $sender->sendMessage(Translation::getMessage("notEnoughMoney"));
            return;
        }
        $senderFaction->subtractMoney($amount);
        $sender->getDataSession()->addToBalance($amount);
        foreach($senderFaction->getOnlineMembers() as $member) {
            $member->sendMessage(Translation::getMessage("withdraw", [
                "name" => TextFormat::GREEN . $sender->getName(),
                "amount" => TextFormat::LIGHT_PURPLE . "$$amount"
            ]));
        }
    }
}