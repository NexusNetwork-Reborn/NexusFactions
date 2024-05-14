<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands\Admin;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;

class SetPowerSubCommand extends SubCommand {

    /**
     * SetPowerSubCommand constructor.
     */
    public function __construct() {
        parent::__construct("setpower", "/faction setpower <faction> <amount>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(($sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR) and $sender instanceof NexusPlayer) or $sender instanceof ConsoleCommandSender) {
            if(isset($args[2])) {
                if (!$sender->isLoaded()) {
                    $sender->sendMessage(Translation::getMessage("noPermission"));
                    return;
                }
                $faction = $this->getCore()->getPlayerManager()->getFactionHandler()->getFaction($args[1]);
                if($faction === null) {
                    $sender->sendMessage("invalidFaction");
                    return;
                }
                $amount = (int)$args[2];
                if(!is_numeric($amount)) {
                    $sender->sendMessage(Translation::getMessage("invalidAmount"));
                    return;
                }
                $faction->scheduleUpdate();
                $faction = $faction->getName();
                $sender->sendMessage(Translation::getMessage("factionAddPowerSuccess", [
                    "amount" => TextFormat::LIGHT_PURPLE . $amount,
                    "name" => $faction
                ]));
                Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getFaction($faction)->setStrength($amount);
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
