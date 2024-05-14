<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\permission\DefaultPermissionNames;
use ReflectionClass;
use ReflectionException;

class PlaySoundCommand extends Command {

    /**
     * PlaySoundCommand constructor.
     */
    public function __construct() {
        parent::__construct("playsound", "Play a sound (For testing purposes)", "/playsound <name: string>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     * @throws ReflectionException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender instanceof NexusPlayer) or (!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR))) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }
        $sender->playSound($args[0], 2, 1);
    }
}