<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Management;

use Xekvern\Core\Command\Utils\Args\RawStringArgument;
use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Faction\FactionException;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class CreateSubCommand extends SubCommand
{

    /**
     * CreateSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("create", "/faction create <name>");
        $this->registerArgument(0, new RawStringArgument("faction"));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws FactionException
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
        if(!$sender->getDataSession()->getBalance() >= 10000) {
            $sender->sendMessage(Translation::RED . "You need atleast $10,000 to create a faction!");
            return;
        }
        if ($sender->getDataSession()->getFaction() !== null) {
            $sender->sendMessage(Translation::getMessage("mustLeaveFaction"));
            return;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        if (strlen($args[1]) > 16) {
            $sender->sendMessage(Translation::getMessage("factionNameTooLong"));
            return;
        }
        if (!preg_match("/^[a-zA-Z]+$/", $args[1])) {
            $sender->sendMessage(Translation::getMessage("onlyLetters"));
            return;
        }
        if ($this->getCore()->getPlayerManager()->getFactionHandler()->factionExists($args[1])) {
            $sender->sendMessage(Translation::getMessage("existingFaction", [
                "faction" => TextFormat::RED . $args[1]
            ]));
            return;
        }
        $this->getCore()->getPlayerManager()->getFactionHandler()->createFaction($args[1], $sender);
        $sender->sendMessage(Translation::getMessage("factionCreate"));
        $sender->getDataSession()->subtractFromBalance(10000);
    }
}