<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Vault\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use Xekvern\Core\Player\Vault\Command\SubCommands\ViewSubCommand;
use Xekvern\Core\Player\Vault\Forms\VaultListForm;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;
use Xekvern\Core\Nexus;

class PlayerVaultCommand extends Command {

    /**
     * PlayerVaultCommand constructor.
     */
    public function __construct() {
        parent::__construct("playervault", "Manage vaults", "/pv <number>", ["pv"]);
        $this->addSubCommand(new ViewSubCommand());
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
            $amount = $args[0];
            if(!is_numeric($amount)) {
                $subCommand = $this->getSubCommand($args[0]);
                if($subCommand !== null) {
                    $subCommand->execute($sender, $commandLabel, $args);
                    return;
                }
                if(!preg_match("/^[a-zA-Z]+$/", $amount)) {
                    if($sender->hasPermission("permission.admin")) {
                        $sender->sendMessage(Translation::getMessage("usageMessage", [
                            "usage" => $this->getUsage() . " <id/alias> or /pv view <player>"
                        ]));
                        return;
                    }
                    else {
                        $sender->sendMessage(Translation::getMessage("noPermission"));
                        return;
                    }
                }
                else {
                    $vault = $sender->getDataSession()->getVaultByAlias((string)$amount);
                }
            }
            else {
                $amount = (int)$amount;
                if($amount <= 0) {
                    $sender->sendMessage(Translation::getMessage("noPermission"));
                    return;
                }
                $vault = $sender->getDataSession()->getVaultById($amount);
            }
            if($vault !== null) {
                $vault->getMenu()->send($sender);
                return;
            }
            else {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
        }
        if($sender->hasPermission("permission.admin")) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage() . " <id/alias> or /pv view <player>"
            ]));
        }
        $sender->sendForm(new VaultListForm($sender));
        $sender->sendMessage(Translation::getMessage("usageMessage", [
            "usage" => $this->getUsage()
        ]));
    }
}