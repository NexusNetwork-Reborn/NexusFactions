<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Item\Enchantment\Enchantment;

class FloatXYZCommand extends Command {

    /**
     * FloatXYZCommand constructor.
     */
    public function __construct() {
        parent::__construct("floatxyz", "Has a mysterious function, only could be executed by Xekvern.", "/floatxyz");
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
        if(!$sender->hasPermission(DefaultPermissionNames::GROUP_OPERATOR)) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->sendMessage(TextFormat::YELLOW . "X: " . (float)$sender->getPosition()->x . "Y: " . (float)$sender->getPosition()->y . "Z: " . (float)$sender->getPosition()->z);
    }
}