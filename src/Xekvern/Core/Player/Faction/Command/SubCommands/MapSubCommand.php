<?php

declare(strict_types = 1);

namespace Xekvern\Core\Player\Faction\Command\SubCommands;

use Xekvern\Core\Command\Utils\SubCommand;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class MapSubCommand extends SubCommand
{

    /**
     * MapSubCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("map", "/faction map");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NexusPlayer) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if ($sender->isSpectator()) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        //$sender->sendMessage(Translation::RED . "Not allowed to use yet!");
        $sender->toggleFMapHUD();
    }
}