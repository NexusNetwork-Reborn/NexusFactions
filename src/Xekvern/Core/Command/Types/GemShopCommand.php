<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissionNames;
use Xekvern\Core\Server\NPC\Inventory\MysteriousItemsInventory;

class GemShopCommand extends Command {

    /**
     * GemShopCommand constructor.
     */
    public function __construct() {
        parent::__construct("gemshop", "Purchase in-game items with gems.", "/gemshop");
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
        $sender->removeCurrentWindow();
        $sender->sendDelayedWindow(new MysteriousItemsInventory());
    }
}