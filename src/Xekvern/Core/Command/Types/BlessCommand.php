<?php

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

class BlessCommand extends Command {

    /**
     * BlessCommand constructor.
     */
    public function __construct() {
        parent::__construct("bless", "Remove negative effects.");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof NexusPlayer) or ((!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) and (!$sender->hasPermission("permission.bless")))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $cooldown = 60 - (time() - $sender->getLastBless());
        if($cooldown > 0) {
            $sender->sendMessage(Translation::getMessage("actionCooldown", [
                "amount" => TextFormat::RED . $cooldown
            ]));                  
            return;
        }
        $player = $sender;
            if ($this->removeNegativeEffects($player)) {
                $player->sendMessage(Translation::GREEN . "Removed bad effects");
                $sender->setLastBless();
            } else {
                $player->sendMessage(Translation::RED . "No bad effects to remove!");
        }
    }

    private function removeNegativeEffects(NexusPlayer $player): bool {
        $hasNegativeEffects = false;
        foreach ($player->getEffects()->all() as $effect) {
            if ($effect->getType()->isBad()) {
                $player->getEffects()->remove($effect->getType());
                $hasNegativeEffects = true;
            }
        }
        return $hasNegativeEffects;
    }
}