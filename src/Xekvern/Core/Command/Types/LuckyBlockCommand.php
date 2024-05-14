<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Arguments\OnOrOffArgument;
use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;

class LuckyBlockCommand extends Command {

    /**
     * BordersCommand constructor.
     */
    public function __construct() {
        parent::__construct("luckyblock", "Toggles lucky blocks", "/luckyblock <on|off>", ["lb"]);
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
        if((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) and (!$sender->hasPermission("permission.luckyblock")))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        switch($args[0]) {
            case "on":
                $sender->setLuckyBlockToggle(true);
                $sender->sendMessage(Translation::getMessage("LuckyBlockToggleChange", [
                    "mode" => $args[0]
                ]));
                return;
            case "off":
                $sender->setLuckyBlockToggle(false);
                $sender->sendMessage(Translation::getMessage("LuckyBlockToggleChange", [
                    "mode" => $args[0]
                ]));
                return;
            default:
                $sender->sendMessage(Translation::getMessage("usageMessage", [
                    "usage" => $this->getUsage()
                ]));
                $sender->playErrorSound(); 
                return;
        }
    }
}