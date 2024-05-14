<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Fund\Command;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use Xekvern\Core\Server\Fund\Inventory\FundInventory;

class FundCommand extends Command
{

    /**
     * FundCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("fund", "Manage goal funds", "/fund", ["goals"]);
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
        $inventory = new FundInventory();
        $inventory->send($sender);
    }
}