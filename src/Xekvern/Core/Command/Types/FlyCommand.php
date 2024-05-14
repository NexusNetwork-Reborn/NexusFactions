<?php

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class FlyCommand extends Command {

    /**
     * FlyCommand constructor.
     */
    public function __construct() {
        parent::__construct("fly", "Modify flight mode");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) and (!$sender->hasPermission("permission.fly")))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if($sender->getWorld()->getFolderName() === "warzone") {
            $sender->sendMessage(Translation::RED . "You cannot fly in pvp!");
            $sender->sendTitle(TextFormat::BOLD . TextFormat::RED . "Fly Disabled", TextFormat::RED . "You can't use this in the warzone world!");
            $sender->playErrorSound();
            return;
        }
        if($sender->getAllowFlight() === true) {
            $sender->setAllowFlight(false);
            $sender->setFlying(false);
        }
        else {
            $sender->setAllowFlight(true);
            $sender->setFlying(true);
        }
        $sender->sendMessage(Translation::getMessage("flightToggle"));
    }
}