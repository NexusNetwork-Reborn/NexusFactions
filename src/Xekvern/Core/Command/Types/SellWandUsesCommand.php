<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Types;

use Xekvern\Core\Command\Utils\Command;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SellWandUsesCommand extends Command {

    /**
     * SellWandUsesCommand constructor.
     */
    public function __construct() {
        parent::__construct("sellwanduses", "Show your sell wand uses.", "/sellwanduses", ["swuses"]);
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
        $sender->sendMessage(Translation::getMessage("sellWandUses", [
            "amount" => TextFormat::GREEN . number_format((int)$sender->getDataSession()->getSellWandUses())
        ]));
    }
}