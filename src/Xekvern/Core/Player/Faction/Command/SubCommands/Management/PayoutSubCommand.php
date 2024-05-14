<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Management;

use Xekvern\Core\Command\Utils\Args\RawStringArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;

class PayoutSubCommand extends SubCommand
{

    /**
     * PayoutSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("payout", "/faction payout <email>");
        $this->registerArgument(0, new RawStringArgument("email"));
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
        if (!$sender->isLoaded()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if ($sender->getDataSession()->getFaction() === null) {
            $sender->sendMessage(Translation::getMessage("beInFaction"));
            return;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $email = $args[1];
        if (strlen($email) > 254) {
            $sender->sendMessage(Translation::getMessage("identifierTooLong"));
            return;
        }
        if ($sender->getDataSession()->getFactionRole() !== Faction::LEADER) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->getDataSession()->getFaction()->setPayoutEmail($email);
        $sender->sendMessage(Translation::getMessage("payoutSet", [
            "email" => $sender->getDataSession()->getFaction()->getPayoutEmail()
        ]));
    }
}