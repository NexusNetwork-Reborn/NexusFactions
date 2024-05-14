<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Command;

use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Forms\CEMenuForm;
use Xekvern\Core\Server\Item\Inventory\CEShopInventory;

class CEShopCommand extends Command
{

    /**
     * CEShopCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("ceshop", "View the enchantments shop menu.");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslatonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $sender->sendDelayedWindow(new CEShopInventory());
    }
}
