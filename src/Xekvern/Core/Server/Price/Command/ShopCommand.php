<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price\Command;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;
use Xekvern\Core\Server\Price\Inventory\ShopInventory;
use Xekvern\Core\Server\Price\Inventory\ShopMainInventory;

class ShopCommand extends Command {

    /**
     * ShopCommand constructor.
     */
    public function __construct() {
        parent::__construct("shop", "Open shop menu");
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
        (new ShopInventory())->send($sender);
    }
}