<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Arguments\OnOrOffArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\permission\DefaultPermissionNames;

class XYZCommand extends Command {

    /**
     * XYZCommand constructor.
     */
    public function __construct() {
        parent::__construct("xyz", "Show your coordinates.", "/xyz <on|off>", ["coords"]);
        $this->registerArgument(0, new OnOrOffArgument("mode"));
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
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        if($sender->isInStaffMode()) {
            if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
                $sender->sendMessage(Translation::getMessage("noPermission"));
                return;
            }
        }
        switch($args[0]) {
            case "on":
                $pk = new GameRulesChangedPacket();
                $pk->gameRules = ["showcoordinates" => new BoolGameRule(true, false)];
                $sender->getNetworkSession()->sendDataPacket($pk);
                $sender->sendMessage(Translation::getMessage("coordsShowChange", [
                    "mode" => $args[0]
                ]));
                return;
            case "off":
                $pk = new GameRulesChangedPacket();
                $pk->gameRules = ["showcoordinates" => new BoolGameRule(false, false)];
                $sender->getNetworkSession()->sendDataPacket($pk);
                $sender->sendMessage(Translation::getMessage("coordsShowChange", [
                    "mode" => $args[0]
                ]));
                return;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                return;
        }
    }
}